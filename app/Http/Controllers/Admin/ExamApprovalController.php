<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
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
        // Optionally push to TAO here
        // app(TaoService::class)->publishTest($paper->tao_test_id);
        return back()->with('success', "Exam \"{$paper->title}\" approved and live.");
    }

    public function reject(Request $r, ExamPaper $paper)
    {
        $r->validate(['reason' => 'required|string|max:500']);
        $paper->update(['status' => 'rejected', 'rejection_reason' => $r->reason]);
        return back()->with('success', 'Exam rejected with reason sent to seller.');
    }
}
