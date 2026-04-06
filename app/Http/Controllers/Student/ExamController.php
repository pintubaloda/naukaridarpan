<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\ExamPaper;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    public function start(Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        $activeAttempt = $purchase->attempts()
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        abort_if(! $purchase->canAttempt() && ! $activeAttempt, 403, 'No retakes remaining on this purchase.');

        $latestAttempt = $purchase->attempts()
            ->where('student_id', auth()->id())
            ->latest()
            ->first();

        return view('exam.start', compact('purchase', 'activeAttempt', 'latestAttempt'));
    }

    public function take(Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        $attempt = $purchase->attempts()
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (! $attempt) {
            abort_if(! $purchase->canAttempt(), 403, 'No retakes remaining.');

            $questions = $this->loadQuestions($purchase);
            if (! empty($questions)) {
                shuffle($questions);
            }

            $attempt = ExamAttempt::create([
                'purchase_id'   => $purchase->id,
                'student_id'    => auth()->id(),
                'exam_paper_id' => $purchase->exam_paper_id,
                'status'        => 'in_progress',
                'started_at'    => now(),
                'answers'       => [],
                'question_order'=> array_values(array_map(fn ($question) => $question['serial'], $questions)),
            ]);

            $purchase->increment('retakes_used');
        } else {
            $questions = $this->loadQuestions($purchase);
        }

        $questions = $this->applyQuestionOrder($questions, $attempt->question_order ?? []);
        $savedAnswers = is_array($attempt->answers) ? $attempt->answers : (json_decode((string) $attempt->answers, true) ?: []);

        return view('exam.take', compact('purchase', 'attempt', 'questions', 'savedAnswers'));
    }

    public function autosave(Request $request, Purchase $purchase): JsonResponse
    {
        abort_if($purchase->student_id !== auth()->id(), 403);

        $attempt = $purchase->attempts()
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $answers = $request->input('answers', []);
        $tabSwitchCount = (int) $request->input('tab_switches', $attempt->tab_switch_count);
        $questionTimings = $this->mergeQuestionTimings(
            $attempt->question_timings ?? [],
            $request->input('question_timings', [])
        );
        $bookmarks = $this->normalizeBookmarks($request->input('bookmarked_questions', $attempt->bookmarked_questions ?? []));
        $securityLog = is_array($attempt->security_log) ? $attempt->security_log : (json_decode((string) $attempt->security_log, true) ?: []);
        $securityLog[] = [
            'event' => 'autosave',
            'answered' => count(array_filter($answers, fn ($value) => !empty($value))),
            'saved_at' => now()->toIso8601String(),
        ];

        $attempt->update([
            'answers' => $answers,
            'question_timings' => $questionTimings,
            'bookmarked_questions' => $bookmarks,
            'tab_switch_count' => max($attempt->tab_switch_count, $tabSwitchCount),
            'security_log' => array_slice($securityLog, -50),
        ]);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
            'answered' => count(array_filter($answers, fn ($value) => !empty($value))),
        ]);
    }

    public function submit(Request $r, Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        $attempt = ExamAttempt::where('purchase_id', $purchase->id)
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()->firstOrFail();

        $answers   = $r->input('answers', []);
        $questions = $this->loadQuestions($purchase);
        $totalMarks = $purchase->examPaper->max_marks;
        $negMark    = (float) $purchase->examPaper->negative_marking;
        $questionTimings = $this->mergeQuestionTimings(
            $attempt->question_timings ?? [],
            $r->input('question_timings', [])
        );
        $bookmarks = $this->normalizeBookmarks($r->input('bookmarked_questions', $attempt->bookmarked_questions ?? []));
        [$stats, $breakdown] = $this->evaluateQuestions($questions, $answers, $negMark, $purchase->examPaper->section_negative_rules ?? []);
        $correct = $stats['correct'];
        $wrong = $stats['wrong'];
        $unattempted = $stats['unattempted'];
        $scored = $stats['score'];

        $pct = ($totalMarks > 0) ? round((max(0, $scored) / $totalMarks) * 100, 2) : 0;

        $antiCheatReview = $this->buildAntiCheatReview(
            $attempt->security_log ?? [],
            (int) $r->input('tab_switches', 0),
            $questionTimings,
            $attempt->started_at?->diffInSeconds(now()) ?? 0
        );

        $attempt->update([
            'status'          => 'submitted',
            'submitted_at'    => now(),
            'time_taken_seconds' => $attempt->started_at->diffInSeconds(now()),
            'score'           => max(0, $scored),
            'percentage'      => max(0, $pct),
            'anti_cheat_review' => $antiCheatReview,
            'correct_answers' => $correct,
            'wrong_answers'   => $wrong,
            'unattempted'     => $unattempted,
            'answers'         => $answers,
            'question_order'  => array_values(array_map(fn ($question) => $question['serial'], $questions)),
            'performance_breakdown' => $breakdown,
            'question_timings' => $questionTimings,
            'bookmarked_questions' => $bookmarks,
            'tab_switch_count'=> (int) $r->input('tab_switches', 0),
        ]);

        $paper = $purchase->examPaper;
        $paper->increment('total_attempts');
        $paper->update([
            'avg_score' => round((float) $paper->attempts()->whereNotNull('percentage')->avg('percentage'), 2),
        ]);
        $this->refreshRankingForExam($paper);

        return redirect()->route('student.exam.result', ['attempt' => $attempt->id]);
    }

    public function result(ExamAttempt $attempt)
    {
        abort_if($attempt->student_id !== auth()->id(), 403);
        $questions = $this->applyQuestionOrder(
            $this->loadQuestions($attempt->purchase),
            $attempt->question_order ?? []
        );
        $analysis = $attempt->performance_breakdown ?? $this->buildPerformanceBreakdownOnly(
            $questions,
            is_array($attempt->answers) ? $attempt->answers : (json_decode((string) $attempt->answers, true) ?: []),
            (float) $attempt->examPaper->negative_marking
        );
        $timingInsights = $this->buildTimingInsights(
            $attempt->question_timings ?? [],
            $attempt->time_taken_seconds ?? 0
        );
        $weakAreas = collect($analysis['sections'] ?? [])
            ->filter(fn ($group) => ($group['wrong'] ?? 0) > 0 || ($group['unattempted'] ?? 0) > 0)
            ->sortBy([
                ['accuracy', 'asc'],
                ['wrong', 'desc'],
            ])
            ->take(3)
            ->values();
        $recommendedExams = ExamPaper::query()
            ->approved()
            ->where('id', '!=', $attempt->exam_paper_id)
            ->where('category_id', $attempt->examPaper->category_id)
            ->where(function ($query) use ($attempt, $weakAreas) {
                $query->where('difficulty', $attempt->examPaper->difficulty);

                if ($attempt->examPaper->subject) {
                    $query->orWhere('subject', $attempt->examPaper->subject);
                }

                foreach ($weakAreas as $group) {
                    if (!empty($group['label']) && $group['label'] !== 'General') {
                        $query->orWhere('title', 'like', '%' . $group['label'] . '%')
                            ->orWhere('subject', 'like', '%' . $group['label'] . '%');
                    }
                }
            })
            ->with(['category', 'seller.sellerProfile'])
            ->latest()
            ->take(3)
            ->get();
        $leaderboard = $attempt->examPaper->attempts()
            ->where('status', 'submitted')
            ->whereNotNull('percentage')
            ->with('student')
            ->orderByDesc('percentage')
            ->orderBy('time_taken_seconds')
            ->take(10)
            ->get();
        $review = $attempt->anti_cheat_review ?? [];

        return view('exam.result', compact('attempt', 'questions', 'analysis', 'timingInsights', 'weakAreas', 'recommendedExams', 'leaderboard', 'review'));
    }

    private function loadQuestions(Purchase $purchase): array
    {
        return json_decode((string) $purchase->examPaper->questions_data, true) ?? [];
    }

    private function normalizeAnswerSet(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $values = array_map(fn ($item) => strtoupper(trim((string) $item)), $values);
        $values = array_values(array_filter($values, fn ($item) => $item !== ''));
        sort($values);

        return $values;
    }

    private function applyQuestionOrder(array $questions, array $order): array
    {
        if (empty($order)) {
            return $questions;
        }

        $indexed = [];
        foreach ($questions as $question) {
            $indexed[$question['serial']] = $question;
        }

        $ordered = [];
        foreach ($order as $serial) {
            if (isset($indexed[$serial])) {
                $ordered[] = $indexed[$serial];
                unset($indexed[$serial]);
            }
        }

        return array_merge($ordered, array_values($indexed));
    }

    private function evaluateQuestions(array $questions, array $answers, float $negativeMarking, array $sectionNegativeRules = []): array
    {
        $stats = [
            'correct' => 0,
            'wrong' => 0,
            'unattempted' => 0,
            'score' => 0,
        ];
        $breakdown = [
            'overall' => [
                'correct' => 0,
                'wrong' => 0,
                'unattempted' => 0,
                'score' => 0,
                'total' => count($questions),
            ],
            'question_types' => [],
            'sections' => [],
        ];

        foreach ($questions as $question) {
            $serial = $question['serial'];
            $marks = (float) ($question['marks'] ?? 1);
            $bucket = $this->resolveAnalyticsBucket($question);
            $typeKey = $question['type'] ?? 'unknown';
            $sectionPenalty = $this->resolveNegativeMarkingForSection($bucket, $negativeMarking, $sectionNegativeRules);

            $this->ensureBreakdownBucket($breakdown['question_types'], $typeKey);
            $this->ensureBreakdownBucket($breakdown['sections'], $bucket);

            if (empty($answers[$serial])) {
                $stats['unattempted']++;
                $breakdown['overall']['unattempted']++;
                $breakdown['question_types'][$typeKey]['unattempted']++;
                $breakdown['sections'][$bucket]['unattempted']++;
                continue;
            }

            $given = $this->normalizeAnswerSet($answers[$serial]);
            $correctAnswer = $this->normalizeAnswerSet($question['correct_answer'] ?? null);

            if ($given === $correctAnswer) {
                $stats['correct']++;
                $stats['score'] += $marks;
                $breakdown['overall']['correct']++;
                $breakdown['overall']['score'] += $marks;
                $breakdown['question_types'][$typeKey]['correct']++;
                $breakdown['question_types'][$typeKey]['score'] += $marks;
                $breakdown['sections'][$bucket]['correct']++;
                $breakdown['sections'][$bucket]['score'] += $marks;
            } else {
                $penalty = $sectionPenalty * $marks;
                $stats['wrong']++;
                $stats['score'] -= $penalty;
                $breakdown['overall']['wrong']++;
                $breakdown['overall']['score'] -= $penalty;
                $breakdown['question_types'][$typeKey]['wrong']++;
                $breakdown['question_types'][$typeKey]['score'] -= $penalty;
                $breakdown['sections'][$bucket]['wrong']++;
                $breakdown['sections'][$bucket]['score'] -= $penalty;
                $breakdown['sections'][$bucket]['negative_marking'] = $sectionPenalty;
            }
        }

        foreach (['question_types', 'sections'] as $groupKey) {
            foreach ($breakdown[$groupKey] as $label => &$group) {
                $group['total'] = $group['correct'] + $group['wrong'] + $group['unattempted'];
                $group['accuracy'] = ($group['correct'] + $group['wrong']) > 0
                    ? round(($group['correct'] / ($group['correct'] + $group['wrong'])) * 100, 2)
                    : null;
                $group['label'] = $label;
            }
            unset($group);
        }

        return [$stats, $breakdown];
    }

    private function buildPerformanceBreakdownOnly(array $questions, array $answers, float $negativeMarking): array
    {
        [, $breakdown] = $this->evaluateQuestions($questions, $answers, $negativeMarking);

        return $breakdown;
    }

    private function ensureBreakdownBucket(array &$group, string $label): void
    {
        if (!isset($group[$label])) {
            $group[$label] = [
                'correct' => 0,
                'wrong' => 0,
                'unattempted' => 0,
                'score' => 0,
            ];
        }
    }

    private function resolveAnalyticsBucket(array $question): string
    {
        foreach (['section', 'topic', 'subject', 'chapter'] as $field) {
            if (!empty($question[$field]) && is_string($question[$field])) {
                return trim($question[$field]);
            }
        }

        return 'General';
    }

    private function mergeQuestionTimings(array $existing, array $incoming): array
    {
        foreach ($incoming as $serial => $seconds) {
            $seconds = max(0, (int) $seconds);
            if ($seconds === 0) {
                continue;
            }

            $existing[$serial] = max((int) ($existing[$serial] ?? 0), $seconds);
        }

        ksort($existing);

        return $existing;
    }

    private function buildTimingInsights(array $questionTimings, int $timeTakenSeconds): array
    {
        $questionTimings = array_map(fn ($value) => (int) $value, $questionTimings);
        arsort($questionTimings);
        $totalTracked = array_sum($questionTimings);
        $count = count($questionTimings);

        return [
            'tracked_total_seconds' => $totalTracked,
            'avg_seconds' => $count > 0 ? (int) round($totalTracked / $count) : 0,
            'slowest' => array_slice($questionTimings, 0, 5, true),
            'time_taken_seconds' => $timeTakenSeconds,
        ];
    }

    private function normalizeBookmarks(array $bookmarks): array
    {
        $bookmarks = array_values(array_unique(array_map(fn ($value) => (int) $value, $bookmarks)));
        sort($bookmarks);

        return array_values(array_filter($bookmarks, fn ($value) => $value > 0));
    }

    private function resolveNegativeMarkingForSection(string $section, float $default, array $rules): float
    {
        foreach ($rules as $rule) {
            if (($rule['section'] ?? null) === $section) {
                return (float) ($rule['negative_marking'] ?? $default);
            }
        }

        return $default;
    }

    private function refreshRankingForExam(ExamPaper $paper): void
    {
        $attempts = $paper->attempts()
            ->where('status', 'submitted')
            ->whereNotNull('percentage')
            ->orderByDesc('percentage')
            ->orderBy('time_taken_seconds')
            ->get();

        $total = max($attempts->count(), 1);
        foreach ($attempts as $index => $attempt) {
            $rank = $index + 1;
            $percentile = round((($total - $index) / $total) * 100, 2);
            $attempt->forceFill([
                'rank_position' => $rank,
                'percentile' => $percentile,
            ])->save();
        }
    }

    private function buildAntiCheatReview(array $securityLog, int $tabSwitchCount, array $questionTimings, int $timeTakenSeconds): array
    {
        $alerts = [];
        $avgSeconds = count($questionTimings) > 0 ? array_sum($questionTimings) / count($questionTimings) : 0;

        if ($tabSwitchCount >= 3) {
            $alerts[] = 'Multiple tab switches detected';
        }
        if ($timeTakenSeconds > 0 && $timeTakenSeconds < 120) {
            $alerts[] = 'Submission was unusually fast';
        }
        if ($avgSeconds > 0 && $avgSeconds < 8) {
            $alerts[] = 'Average time per question was unusually low';
        }

        return [
            'risk_level' => count($alerts) >= 2 ? 'high' : (count($alerts) === 1 ? 'medium' : 'low'),
            'alerts' => $alerts,
            'tab_switch_count' => $tabSwitchCount,
            'autosave_events' => count(array_filter($securityLog, fn ($event) => ($event['event'] ?? null) === 'autosave')),
        ];
    }
}
