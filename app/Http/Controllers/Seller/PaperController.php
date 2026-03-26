<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\{ExamPaper, Category};
use App\Services\AI\PaperParserService;
use App\Services\TAO\TaoService;
use App\Jobs\ParseExamPaperJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PaperController extends Controller
{
    public function index()
    {
        $papers = ExamPaper::where('seller_id', auth()->id())
            ->with('category')->orderByDesc('created_at')->paginate(15);
        return view('seller.papers.index', compact('papers'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('seller.papers.create', compact('categories'));
    }

    public function store(Request $r)
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
            'input_type'      => 'required|in:pdf,typed,url',
            'pdf_file'        => 'required_if:input_type,pdf|file|mimes:pdf|max:51200',
            'pdf_url'         => 'required_if:input_type,url|nullable|url',
            'typed_content'   => 'required_if:input_type,typed|nullable|string',
        ]);

        // Calculate student price with platform markup
        $markupPct    = (float) \App\Models\PlatformSetting::get('default_commission', 15);
        $sellerPrice  = (float) $r->seller_price;
        $markup       = round($sellerPrice * $markupPct / 100, 2);
        $studentPrice = $sellerPrice + $markup;

        $paper = ExamPaper::create([
            'seller_id'        => auth()->id(),
            'category_id'      => $r->category_id,
            'title'            => $r->title,
            'subject'          => $r->subject,
            'exam_type'        => $r->exam_type ?? 'mock',
            'slug'             => Str::slug($r->title) . '-' . Str::random(5),
            'description'      => $r->description,
            'language'         => $r->language,
            'source'           => $r->input_type === 'pdf' ? 'upload' : 'typed',
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
            'status'           => 'draft',
            'parse_status'     => 'pending',
        ]);

        // Handle file upload
        $disk = config('filesystems.default', 'local');
        if ($r->input_type === 'pdf' && $r->hasFile('pdf_file')) {
            $path = $r->file('pdf_file')->store("papers/{$paper->id}", $disk);
            $paper->update(['original_file' => $path]);
            // Dispatch background job to parse PDF via Claude API
            ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'url' && $r->pdf_url) {
            $resp = \Illuminate\Support\Facades\Http::timeout(60)->get($r->pdf_url);
            if (! $resp->successful()) {
                return back()->withErrors(['pdf_url' => 'Failed to download PDF from URL.'])->withInput();
            }
            $path = "papers/{$paper->id}/" . Str::uuid() . '.pdf';
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $resp->body());
            $paper->update(['original_file' => $path, 'source' => 'upload']);
            ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'typed' && $r->typed_content) {
            ParseExamPaperJob::dispatch($paper, 'typed', $r->typed_content);
        }

        return redirect()->route('seller.papers.edit', $paper)
            ->with('success', 'Paper created! AI is now parsing your content — check parse status below.');
    }

    public function edit(ExamPaper $paper)
    {
        abort_if($paper->seller_id !== auth()->id(), 403);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('seller.papers.edit', compact('paper', 'categories'));
    }

    public function update(Request $r, ExamPaper $paper)
    {
        abort_if($paper->seller_id !== auth()->id(), 403);
        $r->validate([
            'title'           => 'required|string|max:255',
            'subject'         => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'seller_price'    => 'required|numeric|min:0',
            'duration_minutes'=> 'required|integer|min:10',
            'max_marks'       => 'required|integer|min:10',
            'max_retakes'     => 'required|integer|min:1',
            'difficulty'      => 'required|in:easy,medium,hard',
            'tags'            => 'nullable|string',
        ]);

        $markupPct    = (float) \App\Models\PlatformSetting::get('default_commission', 15);
        $sellerPrice  = (float) $r->seller_price;
        $markup       = round($sellerPrice * $markupPct / 100, 2);

        $paper->update([
            'title'            => $r->title,
            'subject'          => $r->subject,
            'description'      => $r->description,
            'duration_minutes' => $r->duration_minutes,
            'max_marks'        => $r->max_marks,
            'negative_marking' => $r->negative_marking ?? 0,
            'max_retakes'      => $r->max_retakes,
            'difficulty'       => $r->difficulty,
            'seller_price'     => $sellerPrice,
            'platform_markup'  => $markup,
            'student_price'    => $r->boolean('is_free') ? 0 : $sellerPrice + $markup,
            'is_free'          => $r->boolean('is_free'),
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
        ]);

        return back()->with('success', 'Paper updated successfully.');
    }

    public function destroy(ExamPaper $paper)
    {
        abort_if($paper->seller_id !== auth()->id(), 403);
        abort_if(in_array($paper->status, ['approved']), 403, 'Cannot delete an approved paper with purchases.');
        if ($paper->original_file) Storage::disk('s3')->delete($paper->original_file);
        $paper->delete();
        return redirect()->route('seller.papers')->with('success', 'Paper deleted.');
    }

    public function submitForReview(ExamPaper $paper)
    {
        abort_if($paper->seller_id !== auth()->id(), 403);
        abort_if($paper->parse_status !== 'done', 400, 'Paper must finish parsing before submitting for review.');
        $paper->update(['status' => 'pending_review']);
        return back()->with('success', 'Paper submitted for admin review. Usually approved within 24 hours.');
    }

    public function parseStatus(ExamPaper $paper)
    {
        abort_if($paper->seller_id !== auth()->id(), 403);
        return response()->json([
            'status'          => $paper->parse_status,
            'total_questions' => $paper->total_questions,
            'question_types'  => $paper->question_types,
            'log'             => $paper->parse_log,
        ]);
    }
}
