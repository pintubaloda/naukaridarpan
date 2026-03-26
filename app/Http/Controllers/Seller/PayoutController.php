<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\{KYCVerification, PayoutRequest, Purchase};
use App\Services\Payment\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    public function kyc()
    {
        $kyc = auth()->user()->kyc;
        return view('seller.kyc', compact('kyc'));
    }

    public function submitKyc(Request $r)
    {
        $r->validate([
            'pan_number'          => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/',
            'pan_document'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'aadhaar_number'      => 'required|string|size:12|numeric',
            'aadhaar_document'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bank_name'           => 'required|string|max:100',
            'account_number'      => 'required|string|max:30',
            'ifsc_code'           => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_proof_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $panPath     = $r->file('pan_document')->store('kyc/' . auth()->id(), 's3');
        $aadhaarPath = $r->file('aadhaar_document')->store('kyc/' . auth()->id(), 's3');
        $bankPath    = $r->file('bank_proof_document')->store('kyc/' . auth()->id(), 's3');

        KYCVerification::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'pan_number'          => strtoupper($r->pan_number),
                'pan_document'        => $panPath,
                'aadhaar_number'      => $r->aadhaar_number,
                'aadhaar_document'    => $aadhaarPath,
                'bank_name'           => $r->bank_name,
                'account_number'      => $r->account_number,
                'ifsc_code'           => strtoupper($r->ifsc_code),
                'bank_proof_document' => $bankPath,
                'status'              => 'under_review',
                'rejection_reason'    => null,
            ]
        );

        return redirect()->route('seller.kyc')->with('success', 'KYC documents submitted! Verification takes 1-2 business days.');
    }

    public function index()
    {
        $user     = auth()->user();
        $profile  = $user->sellerProfile;
        $payouts  = PayoutRequest::where('seller_id', $user->id)->orderByDesc('created_at')->paginate(15);
        $kyc      = $user->kyc;
        $threshold = (float) \App\Models\PlatformSetting::get('min_payout_threshold', 500);
        return view('seller.payouts', compact('profile', 'payouts', 'kyc', 'threshold'));
    }

    public function earnings()
    {
        $user = auth()->user();
        $settlements = Purchase::whereHas('examPaper', fn($q) => $q->where('seller_id', $user->id))
            ->where('payment_status', 'paid')
            ->with('examPaper')
            ->orderByDesc('created_at')
            ->paginate(20);
        $profile = $user->sellerProfile;
        return view('seller.earnings', compact('settlements', 'profile'));
    }

    public function requestPayout(Request $r)
    {
        $r->validate(['amount' => 'required|numeric|min:1']);
        $service = app(RazorpayService::class);
        $result  = $service->requestPayout(auth()->user(), (float) $r->amount);
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
