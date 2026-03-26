@extends('layouts.app')
@section('title','Payout Requests — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">Payout Requests</h2>
      <p class="text-muted mb-4">Process seller bank transfers</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($payouts->count())
      <div style="display:flex;flex-direction:column;gap:1rem">
        @foreach($payouts as $payout)
        <div class="card card-static card-body">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <div>
              <div style="font-weight:600;font-family:var(--fu)">{{ $payout->seller->name }}</div>
              <div style="font-size:.82rem;color:var(--ink-l);margin-top:.2rem">{{ $payout->seller->email }} · Requested {{ $payout->created_at->format('d M Y') }}</div>
              <div class="g-grid mt-3" style="grid-template-columns:repeat(4,1fr);gap:.75rem;font-size:.82rem;font-family:var(--fu)">
                <div><div class="text-muted">Amount</div><div style="font-size:1.2rem;font-weight:700;color:var(--teal)">₹{{ number_format($payout->amount,0) }}</div></div>
                <div><div class="text-muted">Bank</div><div class="fw-600">{{ $payout->bank_name }}</div></div>
                <div><div class="text-muted">Account</div><div class="fw-600">XXXXXX{{ substr($payout->account_number,-4) }}</div></div>
                <div><div class="text-muted">IFSC</div><div class="fw-600">{{ $payout->ifsc_code }}</div></div>
              </div>
            </div>
            <div style="flex-shrink:0">
              <form action="{{ route('admin.payouts.process',$payout) }}" method="POST">@csrf
                <div style="display:flex;flex-direction:column;gap:.4rem">
                  <input type="text" name="utr_number" class="form-control" style="font-size:.82rem" placeholder="UTR number (for paid)">
                  <textarea name="admin_note" class="form-control" style="font-size:.82rem;min-height:60px" placeholder="Optional note"></textarea>
                  <div style="display:flex;gap:.4rem">
                    <button type="submit" name="action" value="paid" class="btn btn-success btn-sm">✓ Mark Paid</button>
                    <button type="submit" name="action" value="rejected" class="btn btn-danger btn-sm">✗ Reject</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div style="margin-top:1.5rem">{{ $payouts->links() }}</div>
      @else
      <div class="card card-static card-body text-center" style="padding:4rem"><div style="font-size:3rem;margin-bottom:1rem">✅</div><h3>No pending payout requests</h3></div>
      @endif
    </main>
  </div>
</div>
@endsection
