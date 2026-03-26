<?php
namespace App\Services\Payment;

use App\Models\{ExamPaper, Purchase, PayoutRequest, PlatformSetting};
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RazorpayService
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId     = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
    }

    public function createOrder(ExamPaper $paper, User $student): array
    {
        $amountPaisa = (int) ($paper->student_price * 100);
        $orderId     = 'ND_' . strtoupper(Str::random(12));

        $rzpOrder = $this->apiCall('POST', '/orders', [
            'amount'   => $amountPaisa,
            'currency' => 'INR',
            'receipt'  => $orderId,
            'notes'    => ['exam_id' => $paper->id, 'student_id' => $student->id],
        ]);

        $commissionRate = (float) PlatformSetting::get('default_commission', 15);
        $commission     = round($paper->student_price * $commissionRate / 100, 2);

        Purchase::create([
            'student_id'          => $student->id,
            'exam_paper_id'       => $paper->id,
            'order_id'            => $orderId,
            'amount_paid'         => $paper->student_price,
            'platform_commission' => $commission,
            'seller_credit'       => $paper->student_price - $commission,
            'payment_status'      => 'pending',
            'retakes_used'        => 0,
            'retakes_allowed'     => $paper->max_retakes,
        ]);

        return [
            'razorpay_order_id' => $rzpOrder['id'] ?? null,
            'amount'            => $amountPaisa,
            'currency'          => 'INR',
            'order_id'          => $orderId,
            'key_id'            => $this->keyId,
            'exam_title'        => $paper->title,
            'prefill_name'      => $student->name,
            'prefill_email'     => $student->email,
            'prefill_contact'   => $student->phone ?? '',
        ];
    }

    public function verifyAndCapture(string $rzpOrderId, string $rzpPaymentId, string $rzpSignature, string $orderId): bool
    {
        $expected = hash_hmac('sha256', $rzpOrderId . '|' . $rzpPaymentId, $this->keySecret);
        if (! hash_equals($expected, $rzpSignature)) {
            Log::warning('Razorpay signature mismatch', ['order' => $orderId]);
            return false;
        }

        try {
            DB::transaction(function () use ($rzpPaymentId, $orderId) {
                $purchase = Purchase::where('order_id', $orderId)->firstOrFail();
                $purchase->update([
                    'razorpay_payment_id' => $rzpPaymentId,
                    'payment_status'      => 'paid',
                    'settlement_at'       => Carbon::now()->addHours(48),
                ]);

                $paper   = $purchase->examPaper;
                $profile = $paper->seller->sellerProfile;
                if ($profile) {
                    $profile->increment('pending_balance', $purchase->seller_credit);
                    $profile->increment('total_earnings',  $purchase->seller_credit);
                    $profile->increment('total_sales');
                }
                $paper->increment('total_purchases');
            });
            return true;
        } catch (\Exception $e) {
            Log::error('Payment capture failed: ' . $e->getMessage());
            return false;
        }
    }

    public function processSettlements(): int
    {
        $settled = 0;
        Purchase::where('payment_status', 'paid')
            ->where('is_settled', false)
            ->where('settlement_at', '<=', now())
            ->each(function ($purchase) use (&$settled) {
                DB::transaction(function () use ($purchase, &$settled) {
                    $profile = $purchase->examPaper->seller->sellerProfile;
                    if ($profile) {
                        $profile->decrement('pending_balance', $purchase->seller_credit);
                        $profile->increment('wallet_balance',  $purchase->seller_credit);
                    }
                    $purchase->update(['is_settled' => true]);
                    $settled++;
                });
            });
        return $settled;
    }

    public function requestPayout(User $seller, float $amount): array
    {
        $profile   = $seller->sellerProfile;
        $threshold = (float) PlatformSetting::get('min_payout_threshold', 500);

        if (! $profile) return ['success' => false, 'message' => 'Seller profile not found.'];
        if ($profile->wallet_balance < $amount) return ['success' => false, 'message' => 'Insufficient wallet balance.'];
        if ($amount < $threshold) return ['success' => false, 'message' => "Minimum payout is ₹{$threshold}."];

        $kyc = $seller->kyc;
        if (! $kyc || $kyc->status !== 'approved') return ['success' => false, 'message' => 'Complete KYC verification before requesting payout.'];

        DB::transaction(function () use ($seller, $profile, $amount, $kyc) {
            $profile->decrement('wallet_balance', $amount);
            PayoutRequest::create([
                'seller_id'      => $seller->id,
                'amount'         => $amount,
                'status'         => 'pending',
                'bank_name'      => $kyc->bank_name,
                'account_number' => $kyc->account_number,
                'ifsc_code'      => $kyc->ifsc_code,
            ]);
        });

        return ['success' => true, 'message' => 'Payout request submitted. Processed within 2 working days.'];
    }

    private function apiCall(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "{$this->keyId}:{$this->keySecret}",
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $method !== 'GET' ? json_encode($data) : null,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode((string)$result, true) ?? [];
    }
}
