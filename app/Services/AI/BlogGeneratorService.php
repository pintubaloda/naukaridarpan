<?php
namespace App\Services\AI;

use App\Models\BlogPost;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogGeneratorService
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    public ?string $lastError = null;

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
        $this->lastError = null;
        $topics = $this->loadTopicsFromSettings() ?: $this->topics;
        if ($forcedTopic && $forcedCategory) {
            $cat = $forcedCategory;
            $topic = $forcedTopic;
        } else {
            $cat   = array_rand($topics);
            $topic = $topics[$cat][array_rand($topics[$cat])];
        }
        $data  = $this->callClaude($topic, $lang, $cat);
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

    public function generateDraft(string $topic, string $lang, string $cat): ?array
    {
        $this->lastError = null;
        $data = $this->callClaude($topic, $lang, $cat);
        if (is_array($data)) {
            // Keep the admin-entered topic exact instead of letting the model append SEO suffixes.
            $data['title'] = $topic;
            $data['meta_title'] = $topic;
            $data['category'] = $data['category'] ?? $cat;
        }
        return is_array($data) ? $data : null;
    }

    private function loadTopicsFromSettings(): array
    {
        $text = \App\Models\PlatformSetting::get('blog_topics_text', '');
        if ($text) {
            $map = [];
            foreach (preg_split('/\r?\n/', $text) as $line) {
                $line = trim($line);
                if ($line === '' || ! str_contains($line, ':')) continue;
                [$cat, $items] = array_map('trim', explode(':', $line, 2));
                $topics = array_filter(array_map('trim', explode(',', $items)));
                if ($cat && $topics) $map[$cat] = $topics;
            }
            if (! empty($map)) return $map;
        }
        return [];
    }

    private function callClaude(string $topic, string $lang, string $cat): ?array
    {
        $langNote = $lang === 'Hindi'
            ? 'Write the entire article in Hindi (Devanagari). Keep exam names in English.'
            : 'Write in simple English for aspirants from Tier 2/3 Indian cities.';

        try {
            $prompt = <<<PROMPT
You are a professional content writer for Naukaridarpan.com — India's top competitive exam platform.

Write a comprehensive SEO-optimised blog article about: {$topic}
{$langNote}

Return ONLY valid JSON (no markdown, no extra text):
{
  "title": "Use the exact topic text only, without adding prefixes or suffixes",
  "excerpt": "2-sentence summary max 160 chars",
  "category": "{$cat}",
  "tags": ["tag1","tag2","tag3","tag4","tag5"],
  "meta_title": "Use the exact topic text only, without adding prefixes or suffixes",
  "meta_description": "SEO meta description max 160 chars",
  "body": "Full HTML article 900-1200 words using <h2>,<h3>,<p>,<ul>,<li>,<strong>,<table>,<tr>,<th>,<td>. Include: intro, key highlights/dates table, step-by-step guide, eligibility, 4 FAQs, conclusion with CTA to practice on Naukaridarpan.com"
}
PROMPT
;

            if (PlatformSetting::get('ai_enabled', '1') !== '1') {
                $this->lastError = 'AI disabled in settings';
                Log::warning('BlogAI disabled via settings');
                return null;
            }
            $provider = PlatformSetting::get('ai_provider', 'openai');
            $text = $provider === 'gemini'
                ? $this->generateWithGemini($prompt) ?? $this->generateWithOpenAI($prompt, true)
                : $this->generateWithOpenAI($prompt);

            if (! $text) {
                return null;
            }

            $clean = trim(preg_replace('/```(?:json)?\n?/', '', (string)$text), "` \n");
            $d     = json_decode($clean, true);
            if (! is_array($d)) {
                $d = $this->extractJson($clean);
            }
            if (! is_array($d)) { $this->lastError = 'AI did not return valid JSON'; }
            return is_array($d) ? $d : null;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error('BlogAI exception: ' . $e->getMessage());
            return null;
        }
    }

    private function normalizeGeminiModel(string $model): string
    {
        return str_starts_with($model, 'models/') ? substr($model, 7) : $model;
    }

    private function generateWithGemini(string $prompt): ?string
    {
        $key   = PlatformSetting::get('gemini_api_key');
        $model = $this->normalizeGeminiModel(PlatformSetting::get('gemini_model', 'gemini-2.5-flash'));
        if (! $key) {
            $this->lastError = 'Gemini API key missing';
            Log::error('BlogAI Gemini key missing');
            return null;
        }

        $resp = Http::withHeaders([
            'x-goog-api-key' => $key,
            'content-type' => 'application/json',
        ])->timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
        ]);

        if (! $resp->successful()) {
            $status = $resp->status();
            $this->lastError = 'Gemini HTTP ' . $status;
            Log::error('BlogAI Gemini HTTP error ' . $status);
            return null;
        }

        return $resp->json('candidates.0.content.parts.0.text') ?? '';
    }

    private function generateWithOpenAI(string $prompt, bool $fallback = false): ?string
    {
        $key   = PlatformSetting::get('openai_api_key');
        $model = PlatformSetting::get('openai_model', 'gpt-4o-mini');
        if (! $key) {
            if ($fallback && $this->lastError === 'Gemini HTTP 429') {
                $this->lastError = 'Gemini rate limit hit and OpenAI fallback is not configured';
            } else {
                $this->lastError = 'OpenAI API key missing';
            }
            Log::error('BlogAI OpenAI key missing');
            return null;
        }

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that outputs only JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        if (! $resp->successful()) {
            $status = $resp->status();
            if ($fallback && $this->lastError === 'Gemini HTTP 429') {
                $this->lastError = 'Gemini rate limit hit and OpenAI fallback failed with HTTP ' . $status;
            } else {
                $this->lastError = 'OpenAI HTTP ' . $status;
            }
            Log::error('BlogAI OpenAI HTTP error ' . $status);
            return null;
        }

        if ($fallback && $this->lastError === 'Gemini HTTP 429') {
            $this->lastError = null;
        }

        return $resp->json('choices.0.message.content') ?? '';
    }

    private function extractJson(string $text): ?array
    {
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) return null;
        $snippet = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($snippet, true);
        return is_array($decoded) ? $decoded : null;
    }
}
