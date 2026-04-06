<?php

namespace App\Services\Exams;

use App\Models\ExamPaper;
use App\Models\QuestionBankItem;

class QuestionBankSyncService
{
    public function syncFromExamPaper(ExamPaper $paper, array $questions): int
    {
        $bankName = $this->resolveBankName($paper);
        $count = 0;

        foreach ($questions as $index => $question) {
            $serial = (int) ($question['serial'] ?? ($index + 1));
            $correct = $question['correct_answer'] ?? null;
            $correct = is_array($correct)
                ? array_values(array_filter(array_map(fn ($item) => strtoupper(trim((string) $item)), $correct)))
                : (($correct === null || $correct === '') ? [] : [strtoupper(trim((string) $correct))]);

            $options = collect($question['options'] ?? [])
                ->map(function ($option, $optionIndex) {
                    if (is_array($option)) {
                        return [
                            'label' => strtoupper(trim((string) ($option['label'] ?? chr(65 + $optionIndex)))),
                            'text' => trim((string) ($option['text'] ?? '')),
                        ];
                    }

                    return [
                        'label' => chr(65 + $optionIndex),
                        'text' => trim((string) $option),
                    ];
                })
                ->filter(fn ($option) => ($option['label'] ?? '') !== '' || ($option['text'] ?? '') !== '')
                ->values()
                ->all();

            QuestionBankItem::updateOrCreate(
                [
                    'source_exam_paper_id' => $paper->id,
                    'source_question_serial' => $serial,
                ],
                [
                    'created_by' => $paper->seller_id ?: 1,
                    'category_id' => $paper->category_id,
                    'bank_name' => $bankName,
                    'source_exam_title' => $paper->title,
                    'source_exam_year' => $paper->exam_year,
                    'subject' => $question['subject'] ?? $paper->subject,
                    'section' => $question['section'] ?? null,
                    'topic' => $question['topic'] ?? null,
                    'difficulty' => $paper->difficulty ?: 'medium',
                    'question_type' => $question['type'] ?? 'mcq',
                    'interaction_type' => $question['interaction_type'] ?? null,
                    'qti_identifier' => $question['qti_identifier'] ?? null,
                    'question_text' => trim((string) ($question['text'] ?? '')),
                    'options' => $options,
                    'correct_answer' => $correct,
                    'advanced_metadata' => $question['advanced_metadata'] ?? null,
                    'explanation' => $question['explanation'] ?? null,
                    'marks' => (float) ($question['marks'] ?? 1),
                    'negative_marking' => (float) ($paper->negative_marking ?? 0),
                    'tags' => collect(array_merge((array) $paper->tags, array_filter([$bankName, $paper->subject, $paper->exam_year])))
                        ->map(fn ($tag) => trim((string) $tag))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                    'is_active' => true,
                ]
            );

            $count++;
        }

        return $count;
    }

    public function resolveBankName(ExamPaper $paper): string
    {
        $haystack = strtolower(trim(($paper->title ?? '') . ' ' . ($paper->subject ?? '') . ' ' . ($paper->category->name ?? '')));

        return match (true) {
            str_contains($haystack, 'ssc') => 'SSC',
            str_contains($haystack, 'upsc'), str_contains($haystack, 'nda'), str_contains($haystack, 'cds') => 'UPSC',
            str_contains($haystack, 'neet') => 'NEET',
            str_contains($haystack, 'jee') => 'JEE',
            str_contains($haystack, 'gate') => 'GATE',
            str_contains($haystack, 'cat') => 'CAT',
            str_contains($haystack, 'ctet') => 'CTET',
            str_contains($haystack, 'rrb') => 'RRB',
            default => strtoupper(trim((string) ($paper->subject ?: ($paper->category->slug ?? 'GENERAL')))),
        };
    }
}
