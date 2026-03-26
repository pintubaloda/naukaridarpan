<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KYCVerification;
use Illuminate\Http\Request;

class KYCController extends Controller
{
    public function pending()
    {
        $kycs = KYCVerification::where('status', 'under_review')
            ->with('user')->orderByDesc('created_at')->paginate(20);
        return view('admin.kyc-pending', compact('kycs'));
    }

    public function approve(KYCVerification $kyc)
    {
        $kyc->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $kyc->user->sellerProfile?->update(['is_verified' => true]);
        return back()->with('success', 'KYC approved. Seller can now request payouts.');
    }

    public function reject(Request $r, KYCVerification $kyc)
    {
        $r->validate(['reason' => 'required|string|max:500']);
        $kyc->update(['status' => 'rejected', 'rejection_reason' => $r->reason, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return back()->with('success', 'KYC rejected with reason.');
    }
}
