<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\ExamPaper;
use App\Models\ExamAttempt;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $recentPurchases = Purchase::where('student_id', $user->id)->where('payment_status', 'paid')
            ->with(['examPaper.category', 'attempts' => fn ($query) => $query->latest()])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
        $totalPurchases = Purchase::where('student_id', $user->id)->where('payment_status', 'paid')->count();
        $totalAttempts  = ExamAttempt::where('student_id', $user->id)->count();
        $avgScore       = ExamAttempt::where('student_id', $user->id)->whereNotNull('percentage')->avg('percentage');
        return view('student.dashboard', compact('user', 'recentPurchases', 'totalPurchases', 'totalAttempts', 'avgScore'));
    }

    public function myExams()
    {
        $purchases = Purchase::where('student_id', auth()->id())->where('payment_status', 'paid')
            ->with(['examPaper.category', 'examPaper.seller.sellerProfile', 'attempts'])
            ->orderByDesc('created_at')->paginate(12);
        return view('student.my-exams', compact('purchases'));
    }

    public function results()
    {
        $attempts = ExamAttempt::where('student_id', auth()->id())
            ->with('examPaper')->orderByDesc('created_at')->paginate(15);
        return view('student.results', compact('attempts'));
    }

    public function profile()  { return view('student.profile', ['user' => auth()->user()]); }

    public function updateProfile(Request $r)
    {
        $r->validate(['name' => 'required|string|max:100', 'phone' => 'nullable|string|max:15']);
        auth()->user()->update($r->only('name', 'phone'));
        return back()->with('success', 'Profile updated successfully.');
    }

    public function wishlist() { return view('student.wishlist'); }

    public function checkout(Request $r, ExamPaper $examPaper)
    {
        if ($examPaper->status !== 'approved') abort(404);
        if ($examPaper->is_free) {
            Purchase::firstOrCreate(
                ['student_id' => auth()->id(), 'exam_paper_id' => $examPaper->id],
                ['order_id' => 'FREE_' . uniqid(), 'amount_paid' => 0, 'payment_status' => 'paid',
                 'retakes_allowed' => $examPaper->max_retakes, 'seller_credit' => 0, 'platform_commission' => 0]
            );
            return redirect()->route('student.exams')->with('success', 'Free exam added to your library!');
        }
        $service = app(RazorpayService::class);
        $data    = $service->createOrder($examPaper, auth()->user());
        return view('student.checkout', compact('examPaper', 'data'));
    }

    public function paymentSuccess(Request $r)
    {
        $service = app(RazorpayService::class);
        $ok = $service->verifyAndCapture(
            $r->razorpay_order_id, $r->razorpay_payment_id,
            $r->razorpay_signature, $r->order_id
        );
        return $ok
            ? redirect()->route('student.exams')->with('success', 'Payment successful! Exam unlocked.')
            : redirect()->route('student.exams')->with('error', 'Payment verification failed. Please contact support.');
    }

    public function paymentFailed()
    {
        return redirect()->route('student.exams')->with('error', 'Payment failed. Please try again.');
    }

    public function razorpayWebhook(Request $r)
    {
        $sig      = $r->header('X-Razorpay-Signature');
        $body     = $r->getContent();
        $expected = hash_hmac('sha256', $body, config('services.razorpay.webhook_secret'));
        if (! hash_equals($expected, $sig ?? '')) return response('', 400);
        $payload = $r->json()->all();
        if (($payload['event'] ?? '') === 'payment.captured') {
            $pid = $payload['payload']['payment']['entity']['id'] ?? null;
            if ($pid) Purchase::where('razorpay_payment_id', $pid)->update(['payment_status' => 'paid']);
        }
        return response('ok', 200);
    }
}
