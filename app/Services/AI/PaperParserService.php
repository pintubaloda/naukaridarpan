<?php
namespace App\Services\AI;

use App\Models\ExamPaper;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $disk = 'public';
            $binary = Storage::disk($disk)->get($paper->original_file);

            $localText = $this->extractPdfTextLocal($binary);
            if ($localText !== null) {
                $parsed = $this->parseTextRules($paper, $localText, 'Parsed via local pdftotext.');
                if (! empty($parsed['questions'] ?? [])) {
                    return $parsed;
                }
                $paper->update(['parse_log' => 'Local extraction was weak. Falling back to AI parsing…']);
            }

            return $this->parsePdfWithAi($paper, $binary);
        } catch (\Exception $e) {
            return $this->fail($paper, $e->getMessage());
        }
    }

    public function parseText(ExamPaper $paper, string $raw): array
    {
        $paper->update(['parse_status' => 'processing', 'parse_log' => 'Parsing typed content…']);
        try {
            $local = $this->parseTextRules($paper, $raw, 'Parsed typed content via local rules.');
            if (! empty($local['questions'] ?? [])) {
                return $local;
            }

            $provider = PlatformSetting::get('ai_provider', 'openai');
            if ($provider === 'gemini') {
                $key   = PlatformSetting::get('gemini_api_key');
                $model = $this->normalizeGeminiModel(PlatformSetting::get('gemini_model', 'gemini-2.5-flash'));
                if (! $key) return $this->fail($paper, 'Gemini API key missing.');
                $resp = Http::withHeaders([
                    'x-goog-api-key' => $key,
                    'content-type'   => 'application/json',
                ])->timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [[ 'parts' => [[ 'text' => $this->prompt() . "\n\nCONTENT:\n" . $raw ]] ]],
                ]);
                return $this->handleGeminiResponse($paper, $resp);
            }
            $key   = PlatformSetting::get('openai_api_key');
            $model = PlatformSetting::get('openai_model', 'gpt-4o-mini');
            if (! $key) return $this->fail($paper, 'OpenAI API key missing.');
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that outputs only JSON.'],
                    ['role' => 'user', 'content' => $this->prompt() . "\n\nCONTENT:\n" . $raw],
                ],
                'temperature' => 0.2,
            ]);
            return $this->handleOpenAIResponse($paper, $resp);
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
            $binary = $resp->body();

            $localText = $this->extractPdfTextLocal($binary);
            if ($localText !== null) {
                $parsed = $this->parseTextRules($paper, $localText, 'Parsed URL PDF via local pdftotext.');
                if (! empty($parsed['questions'] ?? [])) {
                    return $parsed;
                }
                $paper->update(['parse_log' => 'Local extraction from URL PDF was weak. Falling back to AI parsing…']);
            }

            return $this->parsePdfWithAi($paper, $binary);
        } catch (\Exception $e) {
            return $this->fail($paper, $e->getMessage());
        }
    }

    private function parsePdfWithAi(ExamPaper $paper, string $binary): array
    {
        $provider = PlatformSetting::get('ai_provider', 'openai');
        if ($provider !== 'gemini') {
            return $this->fail($paper, 'Local parser could not extract enough questions and PDF AI parsing requires Gemini.');
        }

        $b64 = base64_encode($binary);
        $key = PlatformSetting::get('gemini_api_key');
        $model = $this->normalizeGeminiModel(PlatformSetting::get('gemini_model', 'gemini-2.5-flash'));
        if (! $key) return $this->fail($paper, 'Gemini API key missing.');

        $resp = Http::withHeaders([
            'x-goog-api-key' => $key,
            'content-type' => 'application/json',
        ])->timeout(180)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
            'contents' => [[
                'parts' => [
                    ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $b64]],
                    ['text' => $this->prompt()],
                ],
            ]],
        ]);

        return $this->handleGeminiResponse($paper, $resp);
    }

    private function handleResponse(ExamPaper $paper, $resp): array
    {
        if (! $resp->successful()) return $this->fail($paper, 'API error: ' . $resp->status());
        $text   = collect($resp->json('content'))->where('type', 'text')->first()['text'] ?? '';
        $parsed = $this->extractJson($text);
        $qs     = $parsed['questions'] ?? [];

        // Store questions in paper
        return $this->storeParsed($paper, $parsed);
    }

    private function handleOpenAIResponse(ExamPaper $paper, $resp): array
    {
        if (! $resp->successful()) return $this->fail($paper, 'OpenAI API error: ' . $resp->status());
        $text = $resp->json('choices.0.message.content') ?? '';
        $parsed = $this->extractJson($text);
        return $this->storeParsed($paper, $parsed);
    }

    private function handleGeminiResponse(ExamPaper $paper, $resp): array
    {
        if (! $resp->successful()) return $this->fail($paper, 'Gemini API error: ' . $resp->status());
        $text = $resp->json('candidates.0.content.parts.0.text') ?? '';
        $parsed = $this->extractJson($text);
        return $this->storeParsed($paper, $parsed);
    }

    private function storeParsed(ExamPaper $paper, array $parsed): array
    {
        $qs = $parsed['questions'] ?? [];
        if (empty($qs)) {
            return $this->fail($paper, 'No questions could be extracted from the provided paper.');
        }

        $paper->update([
            'parse_status'    => 'done',
            'parse_log'       => $parsed['_parse_log'] ?? ('Parsed ' . count($qs) . ' questions.'),
            'total_questions' => count($qs),
            'question_types'  => $this->typeSummary($qs),
            'questions_data'  => json_encode($qs),
            'tao_sync_status' => 'pending',
            'tao_last_error'  => null,
            'duration_minutes'=> $parsed['duration_minutes'] ?? $paper->duration_minutes,
            'max_marks'       => $parsed['total_marks']      ?? $paper->max_marks,
        ]);
        return $parsed;
    }

    private function normalizeGeminiModel(string $model): string
    {
        return str_starts_with($model, 'models/') ? substr($model, 7) : $model;
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

    private function extractPdfTextLocal(string $binary): ?string
    {
        $pdftotext = trim((string) shell_exec('command -v pdftotext 2>/dev/null'));
        if ($pdftotext === '') {
            return null;
        }

        $tmpPdf = storage_path('app/tmp_' . Str::uuid() . '.pdf');
        file_put_contents($tmpPdf, $binary);
        try {
            $text = $this->runPdfToText($pdftotext, $tmpPdf);
            if ($text !== null) {
                return $text;
            }

            $ocrmypdf = trim((string) shell_exec('command -v ocrmypdf 2>/dev/null'));
            if ($ocrmypdf === '') {
                return null;
            }

            $tmpOcrPdf = storage_path('app/tmp_ocr_' . Str::uuid() . '.pdf');
            $ocrCommand = escapeshellcmd($ocrmypdf)
                . ' --skip-text --force-ocr --output-type pdf '
                . escapeshellarg($tmpPdf) . ' '
                . escapeshellarg($tmpOcrPdf)
                . ' 2>/dev/null';
            shell_exec($ocrCommand);

            if (! file_exists($tmpOcrPdf)) {
                return null;
            }

            return $this->runPdfToText($pdftotext, $tmpOcrPdf);
        } finally {
            @unlink($tmpPdf);
            if (isset($tmpOcrPdf)) {
                @unlink($tmpOcrPdf);
            }
        }
    }

    private function runPdfToText(string $pdftotext, string $pdfPath): ?string
    {
        $command = escapeshellcmd($pdftotext) . ' -layout ' . escapeshellarg($pdfPath) . ' -';
        $output = shell_exec($command);
        $text = is_string($output) ? trim($output) : '';

        return strlen($text) > 200 ? $text : null;
    }

    private function parseTextRules(ExamPaper $paper, string $raw, string $log): array
    {
        $normalized = preg_replace("/\r\n?/", "\n", $raw);
        $normalized = preg_replace("/[ \t]+/", ' ', $normalized ?? '');
        $normalized = trim((string) $normalized);

        if ($normalized === '') {
            return [];
        }

        preg_match_all('/(?:^|\n)\s*(\d{1,3})\s*[\.\)]\s+(.+?)(?=(?:\n\s*\d{1,3}\s*[\.\)]\s+)|\z)/su', $normalized, $matches, PREG_SET_ORDER);

        $questions = [];
        foreach ($matches as $match) {
            $serial = (int) $match[1];
            $block = trim($match[2]);
            $options = $this->extractOptions($block);
            $questionText = $options['question'];
            $question = [
                'serial' => $serial,
                'type' => count($options['options']) >= 4 ? 'mcq' : 'short_answer',
                'text' => $questionText,
                'marks' => 1,
                'options' => $options['options'] ?: null,
                'correct_answer' => null,
                'explanation' => null,
                'image_description' => null,
            ];
            if (trim($questionText) !== '') {
                $questions[] = $question;
            }
        }

        if (count($questions) < 5) {
            return [];
        }

        return $this->storeParsed($paper, [
            'questions' => $questions,
            '_parse_log' => $log . ' Extracted ' . count($questions) . ' questions.',
        ]);
    }

    private function extractOptions(string $block): array
    {
        $parts = preg_split('/\s*\(([A-Da-d])\)\s*/u', $block, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (! is_array($parts) || count($parts) < 3) {
            return ['question' => trim($block), 'options' => []];
        }

        $question = trim(array_shift($parts));
        $options = [];
        for ($i = 0; $i < count($parts) - 1; $i += 2) {
            $label = strtoupper(trim($parts[$i]));
            $text = trim($parts[$i + 1]);
            if ($label !== '' && $text !== '') {
                $options[] = ['label' => $label, 'text' => $text];
            }
        }

        return ['question' => $question, 'options' => $options];
    }
}
