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
}
