<?php
namespace App\Services\AI;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogGeneratorService
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    private array $topics = [
        'Sarkari Result'  => ['SSC CGL Result 2025','UPSC Final Result 2025','RRB NTPC Result','IBPS PO Result','SBI Clerk Result','State PSC Result'],
        'Admit Card'      => ['SSC CHSL Admit Card 2025','UPSC Prelims Admit Card','RRB NTPC Admit Card','IBPS Clerk Admit Card'],
        'Vacancy'         => ['Government Jobs 2025 India','SSC CGL 2025 Notification','Railway Bharti 2025','Police Constable Bharti 2025','Teaching Jobs India 2025'],
        'Exam Date'       => ['SSC CGL 2025 Exam Date','UPSC 2025 Calendar','Banking Exam Schedule 2025'],
        'Answer Key'      => ['SSC CGL Answer Key 2025','UPSC Prelims Answer Key','Railway NTPC Answer Key'],
        'Study Tips'      => ['How to crack UPSC in first attempt','SSC CGL Maths strategy','Bank PO English preparation','Reasoning tricks for SSC CGL'],
        'Current Affairs' => ['Daily Current Affairs India','Monthly GK Digest for Competitive Exams','Important Government Schemes 2025'],
    ];

    public function generateDailyPost(string $lang = 'English', ?string $forcedTopic = null, ?string $forcedCategory = null): ?BlogPost
    {
        $topics = $this->loadTopicsFromSettings() ?: $this->topics;
        if ($forcedTopic && $forcedCategory) {
            $cat = $forcedCategory;
            $topic = $forcedTopic;
        } else {
            $cat   = array_rand($topics);
            $topic = $topics[$cat][array_rand($topics[$cat])];
        }
        $data  = $this->callClaude("$topic — " . now()->format('F Y'), $lang, $cat);
        if (! $data) return null;

        return BlogPost::create([
            'title'            => $data['title'],
            'slug'             => Str::slug($data['title']) . '-' . now()->format('Y-m-d'),
            'excerpt'          => $data['excerpt'],
            'content'          => $data['body'],
            'tags'             => $data['tags'] ?? [],
            'category'         => $data['category'],
            'meta_title'       => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'is_ai_generated'  => true,
            'status'           => 'draft',
        ]);
    }

    private function loadTopicsFromSettings(): array
    {
        $json = \App\Models\PlatformSetting::get('blog_topics_json', '');
        if (! $json) return [];
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function callClaude(string $topic, string $lang, string $cat): ?array
    {
        $langNote = $lang === 'Hindi'
            ? 'Write the entire article in Hindi (Devanagari). Keep exam names in English.'
            : 'Write in simple English for aspirants from Tier 2/3 Indian cities.';

        try {
            $resp = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 2500,
                'messages'   => [['role' => 'user', 'content' => <<<PROMPT
You are a professional content writer for Naukaridarpan.com — India's top competitive exam platform.

Write a comprehensive SEO-optimised blog article about: {$topic}
{$langNote}

Return ONLY valid JSON (no markdown, no extra text):
{
  "title": "SEO title max 60 chars",
  "excerpt": "2-sentence summary max 160 chars",
  "category": "{$cat}",
  "tags": ["tag1","tag2","tag3","tag4","tag5"],
  "meta_title": "SEO meta title max 60 chars",
  "meta_description": "SEO meta description max 160 chars",
  "body": "Full HTML article 900-1200 words using <h2>,<h3>,<p>,<ul>,<li>,<strong>,<table>,<tr>,<th>,<td>. Include: intro, key highlights/dates table, step-by-step guide, eligibility, 4 FAQs, conclusion with CTA to practice on Naukaridarpan.com"
}
PROMPT
                ]],
            ]);

            if (! $resp->successful()) { Log::error('BlogAI HTTP error ' . $resp->status()); return null; }
            $text  = collect($resp->json('content'))->where('type','text')->first()['text'] ?? '';
            $clean = trim(preg_replace('/```(?:json)?\n?/', '', $text), "` \n");
            $d     = json_decode($clean, true);
            return is_array($d) ? $d : null;
        } catch (\Exception $e) {
            Log::error('BlogAI exception: ' . $e->getMessage());
            return null;
        }
    }
}
