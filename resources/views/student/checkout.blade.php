@extends('layouts.app')
@section('title','Checkout — '.$examPaper->title)
@section('content')
<div class="container" style="padding:2rem 1.25rem 4rem;max-width:900px">
  <h2 class="mb-4">Complete Your Purchase</h2>
  <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">
    <div class="card card-static card-body">
      <h3 style="font-size:1rem;margin-bottom:1.25rem">Order Summary</h3>
      <div style="display:flex;gap:1rem;align-items:flex-start;padding-bottom:1.25rem;border-bottom:1px solid var(--border-l);margin-bottom:1.25rem">
        <div style="width:60px;height:60px;background:var(--teal-l);border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0">📝</div>
        <div>
          <div style="font-weight:600;font-family:var(--fu);font-size:.95rem">{{ $examPaper->title }}</div>
          <div style="font-size:.82rem;color:var(--ink-l);margin-top:.3rem">{{ $examPaper->category->name }} · {{ $examPaper->total_questions }} Questions · {{ $examPaper->duration_minutes }} min</div>
          <div style="margin-top:.4rem"><span class="badge badge-gray">{{ ucfirst($examPaper->difficulty) }}</span> <span class="badge badge-teal">{{ $examPaper->max_retakes }} Retakes</span></div>
        </div>
      </div>
      @foreach([['Paper Price','₹'.number_format($examPaper->seller_price,0)],['Platform Fee','₹'.number_format($examPaper->platform_markup,0)]] as [$l,$v])
      <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.4rem 0;color:var(--ink-m)"><span>{{ $l }}</span><span>{{ $v }}</span></div>
      @endforeach
      <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;padding:.75rem 0;margin-top:.25rem;border-top:2px solid var(--border);color:var(--teal)">
        <span>Total</span><span>₹{{ number_format($examPaper->student_price,0) }}</span>
      </div>
      <div class="alert alert-info mt-2" style="font-size:.82rem">Powered by Razorpay — UPI, Debit/Credit Card, Netbanking, Wallet</div>
    </div>
    <div class="card card-static card-body" style="position:sticky;top:80px">
      <h3 style="font-size:1rem;margin-bottom:1.25rem">Pay Securely</h3>
      <button id="pay-btn" class="btn btn-primary btn-block btn-lg" onclick="openRazorpay()">Pay ₹{{ number_format($examPaper->student_price,0) }} →</button>
      <div style="margin-top:1rem;text-align:center">
        @foreach(['✅ Instant access after payment','✅ {{ $examPaper->max_retakes }} retakes included','✅ Secure Razorpay gateway','✅ Refund within 7 days if not satisfied'] as $f)
        <div style="font-size:.8rem;color:var(--ink-l);padding:.2rem 0;font-family:var(--fu)">{{ $f }}</div>
        @endforeach
      </div>
      <form id="payment-form" action="{{ route('student.payment.success') }}" method="GET" style="display:none">
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        <input type="hidden" name="razorpay_signature" id="rzp_signature">
        <input type="hidden" name="order_id" value="{{ $data['order_id'] }}">
      </form>
    </div>
  </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function openRazorpay(){
  const options={
    key:'{{ $data['key_id'] }}',
    amount:{{ $data['amount'] }},
    currency:'INR',
    name:'Naukaridarpan',
    description:'{{ addslashes($data['exam_title']) }}',
    order_id:'{{ $data['razorpay_order_id'] }}',
    handler:function(resp){
      document.getElementById('rzp_order_id').value=resp.razorpay_order_id;
      document.getElementById('rzp_payment_id').value=resp.razorpay_payment_id;
      document.getElementById('rzp_signature').value=resp.razorpay_signature;
      document.getElementById('payment-form').submit();
    },
    prefill:{name:'{{ addslashes($data['prefill_name']) }}',email:'{{ $data['prefill_email'] }}',contact:'{{ $data['prefill_contact'] }}'},
    theme:{color:'#E8650A'}
  };
  new Razorpay(options).open();
}
</script>
@endsection
