<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use App\Models\ExamPaperTaoSyncLog;
use App\Services\TAO\TaoService;
use Illuminate\Http\Request;

class ExamApprovalController extends Controller
{
    public function pending()
    {
        $papers = ExamPaper::where('status', 'pending_review')
            ->with(['seller.sellerProfile', 'category'])
            ->orderByDesc('created_at')->paginate(20);
        return view('admin.exams-pending', compact('papers'));
    }

    public function approve(ExamPaper $paper)
    {
        $paper->update(['status' => 'approved', 'rejection_reason' => null]);

        $tao = app(TaoService::class);
        if ($tao->isConfigured() && $paper->isReadyForTaoSync()) {
            $result = $tao->syncExamPaper($paper);
            $paper->update([
                'tao_test_id' => $result['test_id'] ?? $paper->tao_test_id,
                'tao_delivery_id' => $result['delivery_id'] ?? $paper->tao_delivery_id,
                'tao_sync_status' => !empty($result['success']) ? 'synced' : 'failed',
                'tao_synced_at' => !empty($result['success']) ? now() : $paper->tao_synced_at,
                'tao_last_error' => !empty($result['success']) ? null : ($result['message'] ?? 'TAO sync failed.'),
            ]);

            ExamPaperTaoSyncLog::create([
                'exam_paper_id' => $paper->id,
                'user_id' => auth()->id(),
                'trigger' => 'approval',
                'status' => !empty($result['success']) ? 'success' : 'failed',
                'message' => $result['message'] ?? 'TAO sync completed during approval.',
                'request_payload' => [
                    'exam_paper_id' => $paper->id,
                    'title' => $paper->title,
                    'total_questions' => $paper->total_questions,
                    'duration_minutes' => $paper->duration_minutes,
                ],
                'response_payload' => $result,
                'tao_test_id' => $result['test_id'] ?? $paper->tao_test_id,
                'tao_delivery_id' => $result['delivery_id'] ?? $paper->tao_delivery_id,
            ]);
        } elseif ($tao->isConfigured()) {
            ExamPaperTaoSyncLog::create([
                'exam_paper_id' => $paper->id,
                'user_id' => auth()->id(),
                'trigger' => 'approval',
                'status' => 'failed',
                'message' => 'Exam was approved, but TAO sync was skipped because parsed questions are not ready.',
                'request_payload' => [
                    'exam_paper_id' => $paper->id,
                    'title' => $paper->title,
                ],
            ]);
        }

        $message = "Exam \"{$paper->title}\" approved and live.";
        if ($paper->tao_sync_status === 'synced') {
            $message .= ' TAO sync completed.';
        } elseif ($tao->isConfigured()) {
            $message .= ' TAO sync is pending or failed; review the exam details.';
        }

        return back()->with('success', $message);
    }

    public function reject(Request $r, ExamPaper $paper)
    {
        $r->validate(['reason' => 'required|string|max:500']);
        $paper->update(['status' => 'rejected', 'rejection_reason' => $r->reason]);
        return back()->with('success', 'Exam rejected with reason sent to seller.');
    }
}
