<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationRunLog;
use App\Models\AutomationSource;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ExamPaper;
use App\Models\ProfessorLead;
use App\Jobs\ParseExamPaperJob;
use App\Services\Exams\AnswerKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class N8nAutomationController extends Controller
{
    public function syncSources(Request $request)
    {
        $this->authorizeAutomation($request);

        $data = $request->validate([
            'subject' => 'nullable|string|max:255',
            'sources' => 'required|array|min:1',
            'sources.*.name' => 'required|string|max:255',
            'sources.*.source_type' => 'required|string|max:50',
            'sources.*.site_kind' => 'nullable|string|max:100',
            'sources.*.base_url' => 'nullable|url|max:500',
            'sources.*.rss_url' => 'nullable|url|max:500',
            'sources.*.discovery_query' => 'nullable|string|max:255',
            'sources.*.notes' => 'nullable|string',
            'sources.*.is_active' => 'nullable|boolean',
            'sources.*.last_checked_at' => 'nullable|date',
            'sources.*.last_item_at' => 'nullable|date',
        ]);

        $processed = 0;

        foreach ($data['sources'] as $source) {
            AutomationSource::updateOrCreate(
                [
                    'name' => $source['name'],
                    'base_url' => $source['base_url'] ?? null,
                    'rss_url' => $source['rss_url'] ?? null,
                ],
                [
                    'subject' => $data['subject'] ?? null,
                    'source_type' => $source['source_type'],
                    'site_kind' => $source['site_kind'] ?? null,
                    'discovery_query' => $source['discovery_query'] ?? null,
                    'notes' => $source['notes'] ?? null,
                    'is_active' => array_key_exists('is_active', $source) ? (bool) $source['is_active'] : true,
                    'last_checked_at' => $source['last_checked_at'] ?? now(),
                    'last_item_at' => $source['last_item_at'] ?? null,
                ]
            );
            $processed++;
        }

        $this->logRun('source-discovery', 'sources', $data['subject'] ?? null, 'processed', [
            'sources' => $processed,
        ], 'Source discovery sync completed.', $processed);

        return response()->json(['success' => true, 'processed' => $processed]);
    }

    public function importBlogPosts(Request $request)
    {
        $this->authorizeAutomation($request);

        $data = $request->validate([
            'posts' => 'required|array|min:1',
            'posts.*.title' => 'required|string|max:255',
            'posts.*.content' => 'nullable|string',
            'posts.*.excerpt' => 'nullable|string|max:1000',
            'posts.*.featured_image' => 'nullable|url|max:1000',
            'posts.*.category' => 'required|string|max:100',
            'posts.*.subject' => 'nullable|string|max:255',
            'posts.*.tags' => 'nullable|array',
            'posts.*.source_name' => 'nullable|string|max:255',
            'posts.*.source_url' => 'nullable|url|max:1000',
            'posts.*.status' => 'nullable|in:draft,published',
            'posts.*.published_at' => 'nullable|date',
            'posts.*.import_channel' => 'nullable|string|max:100',
        ]);

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($data['posts'] as $postData) {
            $hashBase = ($postData['source_url'] ?? '') . '|' . Str::lower(trim($postData['title']));
            $importHash = sha1($hashBase);
            $status = $postData['status'] ?? 'draft';

            $post = BlogPost::firstOrNew(['import_hash' => $importHash]);
            $wasExisting = $post->exists;

            $post->fill([
                'author_id' => auth()->id(),
                'title' => $postData['title'],
                'slug' => $post->slug ?: Str::slug($postData['title']) . '-' . Str::random(4),
                'excerpt' => $postData['excerpt'] ?? Str::limit(strip_tags((string) ($postData['content'] ?? '')), 220),
                'content' => $postData['content'] ?? ($post->content ?? ''),
                'featured_image' => $postData['featured_image'] ?? null,
                'category' => $postData['category'],
                'subject' => $postData['subject'] ?? null,
                'source_name' => $postData['source_name'] ?? null,
                'source_url' => $postData['source_url'] ?? null,
                'import_hash' => $importHash,
                'import_channel' => $postData['import_channel'] ?? 'n8n',
                'meta_title' => $post->meta_title ?: $postData['title'],
                'meta_description' => $post->meta_description ?: ($postData['excerpt'] ?? null),
                'tags' => $postData['tags'] ?? [],
                'status' => $status,
                'published_at' => $status === 'published' ? ($postData['published_at'] ?? now()) : null,
                'is_ai_generated' => false,
            ]);
            $post->save();

            $processed++;
            $wasExisting ? $updated++ : $created++;
        }

        $this->logRun('blog-import', 'blog', null, 'processed', [
            'created' => $created,
            'updated' => $updated,
        ], 'Blog import completed.', $processed);

        return response()->json(['success' => true, 'processed' => $processed, 'created' => $created, 'updated' => $updated]);
    }

    public function importProfessorLeads(Request $request)
    {
        $this->authorizeAutomation($request);

        $data = $request->validate([
            'subject' => 'nullable|string|max:255',
            'leads' => 'required|array|min:1',
            'leads.*.name' => 'required|string|max:255',
            'leads.*.email' => 'nullable|email|max:255',
            'leads.*.phone' => 'nullable|string|max:30',
            'leads.*.platform' => 'nullable|string|max:100',
            'leads.*.institution' => 'nullable|string|max:255',
            'leads.*.subject' => 'nullable|string|max:255',
            'leads.*.department' => 'nullable|string|max:255',
            'leads.*.designation' => 'nullable|string|max:255',
            'leads.*.profile_url' => 'nullable|url|max:1000',
            'leads.*.source_name' => 'nullable|string|max:255',
            'leads.*.source_url' => 'nullable|url|max:1000',
            'leads.*.notes' => 'nullable|string',
            'leads.*.subscriber_count' => 'nullable|integer|min:0',
            'leads.*.outreach_status' => 'nullable|string|max:50',
        ]);

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($data['leads'] as $leadData) {
            $hashBase = ($leadData['email'] ?? '') . '|' . ($leadData['profile_url'] ?? '') . '|' . Str::lower(trim($leadData['name']));
            $leadHash = sha1($hashBase);

            $lead = ProfessorLead::firstOrNew(['lead_hash' => $leadHash]);
            $wasExisting = $lead->exists;

            $lead->fill([
                'name' => $leadData['name'],
                'email' => $leadData['email'] ?? null,
                'phone' => $leadData['phone'] ?? null,
                'platform' => $leadData['platform'] ?? 'website',
                'institution' => $leadData['institution'] ?? null,
                'subject' => $leadData['subject'] ?? ($data['subject'] ?? null),
                'department' => $leadData['department'] ?? null,
                'designation' => $leadData['designation'] ?? null,
                'profile_url' => $leadData['profile_url'] ?? null,
                'source_name' => $leadData['source_name'] ?? null,
                'source_url' => $leadData['source_url'] ?? null,
                'lead_hash' => $leadHash,
                'notes' => $leadData['notes'] ?? null,
                'subscriber_count' => $leadData['subscriber_count'] ?? 0,
                'outreach_status' => $leadData['outreach_status'] ?? ($lead->outreach_status ?: 'new'),
            ]);
            $lead->save();

            $processed++;
            $wasExisting ? $updated++ : $created++;
        }

        $this->logRun('professor-import', 'professor-leads', $data['subject'] ?? null, 'processed', [
            'created' => $created,
            'updated' => $updated,
        ], 'Professor lead import completed.', $processed);

        return response()->json(['success' => true, 'processed' => $processed, 'created' => $created, 'updated' => $updated]);
    }

    public function bootstrap(Request $request)
    {
        $this->authorizeAutomation($request);

        return response()->json([
            'success' => true,
            'sources' => AutomationSource::where('is_active', true)->orderBy('subject')->orderBy('name')->get(),
            'endpoints' => [
                'sources_sync' => url('/api/v1/automation/sources/sync'),
                'blog_import' => url('/api/v1/automation/blog/import'),
                'professor_leads_import' => url('/api/v1/automation/professor-leads/import'),
                'exam_import' => url('/api/v1/automation/exams/import'),
                'pending_answer_keys' => url('/api/v1/automation/exams/pending-answer-keys'),
            ],
        ]);
    }

    public function importExamPapers(Request $request)
    {
        $this->authorizeAutomation($request);

        $data = $request->validate([
            'exams' => 'required|array|min:1',
            'exams.*.title' => 'required|string|max:255',
            'exams.*.pdf_url' => 'required|url|max:1000',
            'exams.*.source_url' => 'nullable|url|max:1000',
            'exams.*.source_name' => 'nullable|string|max:255',
            'exams.*.description' => 'nullable|string',
            'exams.*.subject' => 'nullable|string|max:255',
            'exams.*.exam_type' => 'nullable|in:mock,previous_year',
            'exams.*.category_slug' => 'nullable|string|max:100',
            'exams.*.language' => 'nullable|in:English,Hindi,Both',
            'exams.*.difficulty' => 'nullable|in:easy,medium,hard',
            'exams.*.tags' => 'nullable|array',
        ]);

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($data['exams'] as $examData) {
            $category = $this->resolveExamCategory($examData['category_slug'] ?? null);
            if (! $category) {
                continue;
            }

            $lookupUrl = $examData['source_url'] ?? $examData['pdf_url'];
            $paper = ExamPaper::firstOrNew([
                'source' => 'scraped',
                'source_url' => $lookupUrl,
            ]);
            $wasExisting = $paper->exists;

            $paper->fill([
                'seller_id' => 1,
                'category_id' => $category->id,
                'title' => $examData['title'],
                'subject' => $examData['subject'] ?? ($category->slug ?? null),
                'exam_type' => $examData['exam_type'] ?? 'previous_year',
                'slug' => $paper->slug ?: Str::slug($examData['title']) . '-' . Str::random(5),
                'description' => $examData['description'] ?? 'Official exam paper imported by n8n. Draft review pending.',
                'language' => $examData['language'] ?? 'English',
                'difficulty' => $examData['difficulty'] ?? 'medium',
                'source' => 'scraped',
                'source_url' => $lookupUrl,
                'is_free' => true,
                'seller_price' => 0,
                'platform_markup' => 0,
                'student_price' => 0,
                'status' => 'draft',
                'parse_status' => 'pending',
                'tags' => $examData['tags'] ?? [$examData['source_name'] ?? 'official', 'imported'],
            ]);
            $paper->save();

            if ($this->syncPaperPdf($paper, $examData['pdf_url'])) {
                ParseExamPaperJob::dispatch($paper, 'pdf');
            }

            $processed++;
            $wasExisting ? $updated++ : $created++;
        }

        $this->logRun('exam-import', 'exams', null, 'processed', [
            'created' => $created,
            'updated' => $updated,
        ], 'Exam import completed.', $processed);

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    public function pendingAnswerKeys(Request $request)
    {
        $this->authorizeAutomation($request);

        $papers = ExamPaper::query()
            ->where('status', 'draft')
            ->whereNotNull('answer_key_pdf_url')
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'title',
                'subject',
                'questions_data',
                'answer_key_pdf_url',
                'source_url',
                'updated_at',
            ])
            ->filter(function (ExamPaper $paper) {
                $questions = $paper->questions_data ? json_decode((string) $paper->questions_data, true) : [];
                if (empty($questions)) {
                    return false;
                }

                return collect($questions)->contains(function ($question) {
                    $correct = $question['correct_answer'] ?? null;
                    return $correct === null || $correct === '' || (is_array($correct) && empty($correct));
                });
            })
            ->values()
            ->map(fn (ExamPaper $paper) => [
                'id' => $paper->id,
                'title' => $paper->title,
                'subject' => $paper->subject,
                'answer_key_pdf_url' => $paper->answer_key_pdf_url,
                'source_url' => $paper->source_url,
                'updated_at' => $paper->updated_at,
            ]);

        return response()->json([
            'success' => true,
            'papers' => $papers,
        ]);
    }

    public function applyExamAnswerKey(Request $request, ExamPaper $paper, AnswerKeyService $answerKeys)
    {
        $this->authorizeAutomation($request);

        $data = $request->validate([
            'answer_key_pdf_url' => 'nullable|url|max:1000',
            'answer_key_text' => 'nullable|string',
            'serial_answers' => 'nullable|array',
        ]);

        $serialAnswers = $data['serial_answers'] ?? [];
        if (empty($serialAnswers) && ! empty($data['answer_key_text'])) {
            $serialAnswers = $answerKeys->extractSerialAnswersFromText($data['answer_key_text']);
        }

        if (empty($serialAnswers)) {
            return response()->json(['success' => false, 'message' => 'No serial answers found to apply.'], 422);
        }

        $result = $answerKeys->applySerialAnswers($paper, $serialAnswers, $data['answer_key_pdf_url'] ?? null);

        $this->logRun('answer-key-apply', 'exams', $paper->subject, 'processed', [
            'paper_id' => $paper->id,
            'updated' => $result['updated'],
            'unmatched' => count($result['unmatched_serials']),
        ], 'Answer key applied to exam.', $result['updated']);

        return response()->json([
            'success' => true,
            'paper_id' => $paper->id,
            'updated' => $result['updated'],
            'unmatched_serials' => $result['unmatched_serials'],
        ]);
    }

    private function authorizeAutomation(Request $request): void
    {
        $expected = config('services.n8n.shared_token');
        $provided = $request->header('X-N8N-Token');

        abort_if(empty($expected) || !hash_equals((string) $expected, (string) $provided), 401, 'Invalid automation token.');
    }

    private function logRun(string $workflowName, ?string $runType, ?string $subject, string $status, array $summary, string $message, int $processedCount): void
    {
        AutomationRunLog::create([
            'workflow_name' => $workflowName,
            'run_type' => $runType,
            'subject' => $subject,
            'status' => $status,
            'payload_summary' => $summary,
            'message' => $message,
            'processed_count' => $processedCount,
        ]);
    }

    private function resolveExamCategory(?string $slug): ?Category
    {
        if ($slug) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                return $category;
            }
        }

        return Category::where('is_active', true)->orderBy('sort_order')->first();
    }

    private function syncPaperPdf(ExamPaper $paper, string $pdfUrl): bool
    {
        $response = Http::timeout(90)->get($pdfUrl);
        if (! $response->successful()) {
            return false;
        }

        $path = "papers/{$paper->id}/" . Str::uuid() . '.pdf';
        Storage::disk('public')->put($path, $response->body());
        $paper->update(['original_file' => $path]);

        return true;
    }
}
