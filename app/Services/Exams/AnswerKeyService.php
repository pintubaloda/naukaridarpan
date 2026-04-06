<?php

namespace App\Services\Exams;

use App\Models\ExamPaper;

class AnswerKeyService
{
    public function applySerialAnswers(ExamPaper $paper, array $serialAnswers, ?string $answerKeyPdfUrl = null): array
    {
        $questions = $paper->questions_data ? json_decode((string) $paper->questions_data, true) : [];
        $normalizedAnswers = $this->normalizeSerialAnswers($serialAnswers);

        $updated = 0;
        $unmatched = [];

        foreach ($questions as &$question) {
            $serial = (int) ($question['serial'] ?? 0);
            if ($serial <= 0 || !array_key_exists($serial, $normalizedAnswers)) {
                continue;
            }

            $question['correct_answer'] = $normalizedAnswers[$serial];
            $updated++;
        }
        unset($question);

        foreach (array_keys($normalizedAnswers) as $serial) {
            $exists = collect($questions)->contains(fn ($question) => (int) ($question['serial'] ?? 0) === (int) $serial);
            if (! $exists) {
                $unmatched[] = $serial;
            }
        }

        $paper->update([
            'questions_data' => json_encode($questions),
            'answer_key_pdf_url' => $answerKeyPdfUrl ?: $paper->answer_key_pdf_url,
            'answer_key_applied_at' => now(),
            'answer_key_parse_log' => 'Applied ' . $updated . ' serial answer(s).' . (! empty($unmatched) ? ' Unmatched serials: ' . implode(', ', $unmatched) . '.' : ''),
        ]);

        return [
            'updated' => $updated,
            'unmatched_serials' => $unmatched,
            'serial_answers' => $normalizedAnswers,
        ];
    }

    public function extractSerialAnswersFromText(string $text): array
    {
        $serialAnswers = [];

        preg_match_all('/(?:^|\n)\s*(\d{1,3})\s*[\.\)\-:]?\s*(?:ans(?:wer)?\s*[:\-]?\s*)?\(?([A-D](?:\s*,\s*[A-D])*)\)?/i', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $serial = (int) ($match[1] ?? 0);
            $answer = strtoupper(trim((string) ($match[2] ?? '')));

            if ($serial > 0 && $answer !== '') {
                $serialAnswers[$serial] = $this->normalizeAnswerValue($answer);
            }
        }

        return $serialAnswers;
    }

    private function normalizeSerialAnswers(array $serialAnswers): array
    {
        $normalized = [];

        foreach ($serialAnswers as $serial => $answer) {
            $serial = (int) $serial;
            if ($serial <= 0) {
                continue;
            }

            $value = $this->normalizeAnswerValue($answer);
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $normalized[$serial] = $value;
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeAnswerValue(mixed $answer): mixed
    {
        if (is_array($answer)) {
            $values = collect($answer)
                ->map(fn ($item) => strtoupper(trim((string) $item)))
                ->filter()
                ->values()
                ->all();

            return count($values) <= 1 ? ($values[0] ?? null) : $values;
        }

        $answer = strtoupper(trim((string) $answer));
        if ($answer === '') {
            return null;
        }

        if (str_contains($answer, ',')) {
            $parts = array_values(array_filter(array_map(fn ($item) => strtoupper(trim((string) $item)), explode(',', $answer))));
            return count($parts) <= 1 ? ($parts[0] ?? null) : $parts;
        }

        return $answer;
    }
}
