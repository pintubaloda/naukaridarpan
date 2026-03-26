<?php
namespace App\Services\AI;

use App\Models\ExamPaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaperParserService
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    private function headers(): array
    {
        return [
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ];
    }

    public function parsePdf(ExamPaper $paper): array
    {
        $paper->update(['parse_status' => 'processing', 'parse_log' => 'Reading PDF…']);
        try {
            $disk = config('filesystems.default', 'local');
            $b64  = base64_encode(Storage::disk($disk)->get($paper->original_file));
            $resp = Http::withHeaders($this->headers())->timeout(180)->post($this->apiUrl, [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 8000,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $b64]],
                        ['type' => 'text', 'text' => $this->prompt()],
                    ],
                ]],
            ]);
            return $this->handleResponse($paper, $resp);
        } catch (\Exception $e) {
            return $this->fail($paper, $e->getMessage());
        }
    }

    public function parseText(ExamPaper $paper, string $raw): array
    {
        $paper->update(['parse_status' => 'processing', 'parse_log' => 'Parsing typed content…']);
        try {
            $resp = Http::withHeaders($this->headers())->timeout(120)->post($this->apiUrl, [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 8000,
                'messages'   => [['role' => 'user', 'content' => $this->prompt() . "\n\nCONTENT:\n" . $raw]],
            ]);
            return $this->handleResponse($paper, $resp);
        } catch (\Exception $e) {
            return $this->fail($paper, $e->getMessage());
        }
    }

    public function parseUrl(ExamPaper $paper, string $url): array
    {
        $paper->update(['parse_status' => 'processing', 'parse_log' => 'Fetching PDF from URL…']);
        try {
            $resp = Http::timeout(60)->get($url);
            if (! $resp->successful()) return $this->fail($paper, 'Failed to download PDF: ' . $resp->status());
            $b64  = base64_encode($resp->body());
            $ai   = Http::withHeaders($this->headers())->timeout(180)->post($this->apiUrl, [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 8000,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $b64]],
                        ['type' => 'text', 'text' => $this->prompt()],
                    ],
                ]],
            ]);
            return $this->handleResponse($paper, $ai);
        } catch (\Exception $e) {
            return $this->fail($paper, $e->getMessage());
        }
    }

    private function handleResponse(ExamPaper $paper, $resp): array
    {
        if (! $resp->successful()) return $this->fail($paper, 'API error: ' . $resp->status());
        $text   = collect($resp->json('content'))->where('type', 'text')->first()['text'] ?? '';
        $parsed = $this->extractJson($text);
        $qs     = $parsed['questions'] ?? [];

        // Store questions in paper
        $paper->update([
            'parse_status'    => 'done',
            'parse_log'       => 'Parsed ' . count($qs) . ' questions via Claude AI.',
            'total_questions' => count($qs),
            'question_types'  => $this->typeSummary($qs),
            'questions_data'  => json_encode($qs),
            'duration_minutes'=> $parsed['duration_minutes'] ?? $paper->duration_minutes,
            'max_marks'       => $parsed['total_marks']      ?? $paper->max_marks,
        ]);
        return $parsed;
    }

    private function fail(ExamPaper $paper, string $msg): array
    {
        Log::error("PaperParser failed [{$paper->id}]: $msg");
        $paper->update(['parse_status' => 'failed', 'parse_log' => $msg]);
        return [];
    }

    private function extractJson(string $t): array
    {
        $clean = trim(preg_replace('/```(?:json)?\n?/', '', $t), "` \n");
        return json_decode($clean, true) ?? ['questions' => []];
    }

    private function typeSummary(array $qs): array
    {
        $c = [];
        foreach ($qs as $q) { $t = $q['type'] ?? 'mcq'; $c[$t] = ($c[$t] ?? 0) + 1; }
        return $c;
    }

    private function prompt(): string
    {
        return <<<'PROMPT'
You are an expert Indian competitive exam paper parser (UPSC, SSC, Banking, Railway, State PSC, NEET, JEE).

Extract ALL questions and return ONLY valid JSON — no preamble, no markdown fences.

{
  "exam_title": "string",
  "subject": "string",
  "total_marks": number,
  "duration_minutes": number,
  "negative_marking": number,
  "instructions": "string",
  "questions": [
    {
      "serial": 1,
      "type": "mcq|msq|short_answer|long_answer|fill_blank|match|omr|math",
      "text": "question text — preserve LaTeX: \\( \\) inline, \\[ \\] block",
      "marks": 1,
      "options": [
        {"label": "A", "text": "option text"},
        {"label": "B", "text": "option text"},
        {"label": "C", "text": "option text"},
        {"label": "D", "text": "option text"}
      ],
      "correct_answer": "A",
      "explanation": "if available or null",
      "image_description": "describe diagram/figure or null"
    }
  ]
}

Rules:
- mcq = single correct answer (string), msq = multiple correct (array)
- Preserve all LaTeX math notation exactly
- For fill_blank: options=null, correct_answer=the answer phrase
- For match: options contain matching items
- Extract every question even in complex multi-column layouts
- If no answer key present, set correct_answer=null
- Return only JSON, absolutely nothing else
PROMPT;
    }
}
