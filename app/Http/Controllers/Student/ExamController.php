<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\ExamAttempt;
use App\Services\TAO\TaoService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function start(Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        abort_if(! $purchase->canAttempt(), 403, 'No retakes remaining on this purchase.');
        return view('exam.start', compact('purchase'));
    }

    public function take(Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        abort_if(! $purchase->canAttempt(), 403, 'No retakes remaining.');
        $attempt = ExamAttempt::create([
            'purchase_id'   => $purchase->id,
            'student_id'    => auth()->id(),
            'exam_paper_id' => $purchase->exam_paper_id,
            'status'        => 'in_progress',
            'started_at'    => now(),
        ]);
        $purchase->increment('retakes_used');

        $taoLaunchUrl = null;
        $tao = app(TaoService::class);
        if ($tao->isConfigured() && $purchase->examPaper->tao_test_id) {
            $delivery = $tao->createDeliveryWithLaunch($purchase->examPaper->tao_test_id, auth()->id());
            $taoLaunchUrl = $delivery['launch_url'] ?? null;
            $attempt->update([
                'tao_delivery_uri' => $delivery['delivery_uri'] ?? null,
                'tao_launch_url'   => $taoLaunchUrl,
            ]);
        }

        $questions = json_decode($purchase->examPaper->questions_data, true) ?? [];
        if (! empty($questions)) shuffle($questions);
        return view('exam.take', compact('purchase', 'attempt', 'questions', 'taoLaunchUrl'));
    }

    public function submit(Request $r, Purchase $purchase)
    {
        abort_if($purchase->student_id !== auth()->id(), 403);
        $attempt = ExamAttempt::where('purchase_id', $purchase->id)
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()->firstOrFail();

        $answers   = $r->input('answers', []);
        $questions = json_decode($purchase->examPaper->questions_data, true) ?? [];
        $totalMarks = $purchase->examPaper->max_marks;
        $negMark    = (float) $purchase->examPaper->negative_marking;
        $correct = $wrong = $unattempted = 0;
        $scored  = 0;

        foreach ($questions as $q) {
            $serial = $q['serial'];
            if (empty($answers[$serial])) { $unattempted++; continue; }
            $given       = is_array($answers[$serial]) ? $answers[$serial] : [$answers[$serial]];
            $correctAns  = is_array($q['correct_answer'] ?? null) ? $q['correct_answer'] : [$q['correct_answer'] ?? ''];
            $marks       = (float) ($q['marks'] ?? 1);
            if (array_map('strtoupper', $given) === array_map('strtoupper', $correctAns)) {
                $correct++; $scored += $marks;
            } else {
                $wrong++; $scored -= ($negMark * $marks);
            }
        }

        $pct = ($totalMarks > 0) ? round((max(0, $scored) / $totalMarks) * 100, 2) : 0;

        $attempt->update([
            'status'          => 'submitted',
            'submitted_at'    => now(),
            'time_taken_seconds' => $attempt->started_at->diffInSeconds(now()),
            'score'           => max(0, $scored),
            'percentage'      => max(0, $pct),
            'correct_answers' => $correct,
            'wrong_answers'   => $wrong,
            'unattempted'     => $unattempted,
            'answers'         => json_encode($answers),
            'tab_switch_count'=> (int) $r->input('tab_switches', 0),
        ]);

        $purchase->examPaper->increment('total_attempts');

        return redirect()->route('student.exam.result', ['attempt' => $attempt->id]);
    }

    public function result(ExamAttempt $attempt)
    {
        abort_if($attempt->student_id !== auth()->id(), 403);
        $tao = app(TaoService::class);
        if ($tao->isConfigured() && $attempt->tao_delivery_uri && empty($attempt->tao_result)) {
            $tao->syncAttemptResult($attempt);
            $attempt->refresh();
        }
        $questions = json_decode($attempt->examPaper->questions_data, true) ?? [];
        return view('exam.result', compact('attempt', 'questions'));
    }
}
