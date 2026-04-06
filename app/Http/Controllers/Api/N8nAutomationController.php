<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationRunLog;
use App\Models\AutomationSource;
use App\Models\BlogPost;
use App\Models\ProfessorLead;
use Illuminate\Http\Request;
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
            ],
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
}
