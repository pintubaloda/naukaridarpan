<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, ExamPaper, Purchase, PayoutRequest, PlatformSetting};
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_sellers'  => User::where('role', 'seller')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_papers'   => ExamPaper::count(),
            'pending_review' => ExamPaper::where('status', 'pending_review')->count(),
            'total_sales'    => Purchase::where('payment_status', 'paid')->count(),
            'total_revenue'  => Purchase::where('payment_status', 'paid')->sum('platform_commission'),
            'pending_kyc'    => \App\Models\KYCVerification::where('status', 'under_review')->count(),
            'pending_payout' => PayoutRequest::where('status', 'pending')->count(),
        ];

        $recentSales = Purchase::where('payment_status', 'paid')
            ->with(['student', 'examPaper.seller'])
            ->orderByDesc('created_at')->take(10)->get();

        $recentPapers = ExamPaper::where('status', 'pending_review')
            ->with(['seller', 'category'])->orderByDesc('created_at')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentSales', 'recentPapers'));
    }

    public function users(Request $r)
    {
        $query = User::query();
        if ($r->role)   $query->where('role', $r->role);
        if ($r->search) $query->where(fn($q) => $q->where('name', 'like', '%'.$r->search.'%')->orWhere('email', 'like', '%'.$r->search.'%'));
        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'User status updated.');
    }

    public function settings()
    {
        $settings = PlatformSetting::all()->keyBy('key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $r)
    {
        foreach ($r->except('_token') as $key => $value) {
            PlatformSetting::set($key, $value);
        }
        return back()->with('success', 'Settings saved.');
    }

    public function pendingPayouts()
    {
        $payouts = PayoutRequest::where('status', 'pending')
            ->with(['seller.sellerProfile', 'seller.kyc'])->orderByDesc('created_at')->paginate(20);
        return view('admin.payouts', compact('payouts'));
    }

    public function processPayout(Request $r, PayoutRequest $payout)
    {
        $r->validate(['action' => 'required|in:paid,rejected', 'utr_number' => 'required_if:action,paid|nullable|string', 'admin_note' => 'nullable|string']);
        $payout->update([
            'status'       => $r->action,
            'utr_number'   => $r->utr_number,
            'admin_note'   => $r->admin_note,
            'processed_at' => now(),
        ]);
        if ($r->action === 'rejected') {
            // Refund to wallet
            $payout->seller->sellerProfile->increment('wallet_balance', $payout->amount);
        }
        return back()->with('success', 'Payout ' . $r->action . '.');
    }

    public function scrapedPapers()
    {
        $papers = ExamPaper::where('source', 'scraped')->where('status', 'draft')
            ->with('category')->orderByDesc('created_at')->paginate(20);
        return view('admin.scraped', compact('papers'));
    }

    public function publishScraped(ExamPaper $paper)
    {
        $paper->update(['status' => 'approved', 'is_free' => true, 'student_price' => 0]);
        return back()->with('success', 'Scraped paper published as free exam.');
    }

    public function professorLeads(Request $r)
    {
        $leads = \App\Models\ProfessorLead::orderByDesc('created_at')->paginate(30);
        return view('admin.professor-leads', compact('leads'));
    }

    public function sendOnboardingMailer(Request $r)
    {
        $r->validate(['lead_ids' => 'required|array', 'template' => 'required|string']);
        // dispatch mailer job
        \App\Jobs\SendOnboardingMailerJob::dispatch($r->lead_ids, $r->template);
        return back()->with('success', count($r->lead_ids) . ' mailers queued.');
    }

    public function createPaper()
    {
        $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.papers.create', compact('categories'));
    }

    public function storePaper(Request $r)
    {
        $r->validate([
            'title'           => 'required|string|max:255',
            'subject'         => 'nullable|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'description'     => 'nullable|string|max:2000',
            'language'        => 'required|in:English,Hindi,Both',
            'duration_minutes'=> 'required|integer|min:10|max:360',
            'max_marks'       => 'required|integer|min:10',
            'negative_marking'=> 'nullable|numeric|min:0|max:1',
            'max_retakes'     => 'required|integer|min:1|max:10',
            'difficulty'      => 'required|in:easy,medium,hard',
            'seller_price'    => 'required|numeric|min:0',
            'is_free'         => 'boolean',
            'tags'            => 'nullable|string',
            'input_type'      => 'required|in:pdf,url,typed',
            'pdf_file'        => 'required_if:input_type,pdf|file|mimes:pdf|max:51200',
            'pdf_url'         => 'required_if:input_type,url|nullable|url',
            'typed_content'   => 'required_if:input_type,typed|nullable|string',
            'publish_now'     => 'nullable|boolean',
        ]);

        $markupPct    = (float) \App\Models\PlatformSetting::get('default_commission', 15);
        $sellerPrice  = (float) $r->seller_price;
        $markup       = round($sellerPrice * $markupPct / 100, 2);
        $studentPrice = $sellerPrice + $markup;

        $paper = \App\Models\ExamPaper::create([
            'seller_id'        => auth()->id(),
            'category_id'      => $r->category_id,
            'title'            => $r->title,
            'subject'          => $r->subject,
            'slug'             => \Illuminate\Support\Str::slug($r->title) . '-' . \Illuminate\Support\Str::random(5),
            'description'      => $r->description,
            'language'         => $r->language,
            'source'           => $r->input_type === 'typed' ? 'typed' : ($r->input_type === 'url' ? 'upload' : 'upload'),
            'duration_minutes' => $r->duration_minutes,
            'max_marks'        => $r->max_marks,
            'negative_marking' => $r->negative_marking ?? 0,
            'max_retakes'      => $r->max_retakes,
            'difficulty'       => $r->difficulty,
            'seller_price'     => $sellerPrice,
            'platform_markup'  => $markup,
            'student_price'    => $r->boolean('is_free') ? 0 : $studentPrice,
            'is_free'          => $r->boolean('is_free'),
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
            'status'           => $r->boolean('publish_now') ? 'approved' : 'draft',
            'parse_status'     => 'pending',
        ]);

        $disk = 'public';
        if ($r->input_type === 'pdf' && $r->hasFile('pdf_file')) {
            $path = $r->file('pdf_file')->store("papers/{$paper->id}", $disk);
            $paper->update(['original_file' => $path]);
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'url' && $r->pdf_url) {
            $resp = \Illuminate\Support\Facades\Http::timeout(60)->get($r->pdf_url);
            if (! $resp->successful()) {
                return back()->withErrors(['pdf_url' => 'Failed to download PDF from URL.'])->withInput();
            }
            $path = "papers/{$paper->id}/" . \Illuminate\Support\Str::uuid() . '.pdf';
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $resp->body());
            $paper->update(['original_file' => $path]);
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'typed' && $r->typed_content) {
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'typed', $r->typed_content);
        }

        return redirect()->route('admin.papers.create')->with('success', 'Paper created. Parsing started.');
    }
}
