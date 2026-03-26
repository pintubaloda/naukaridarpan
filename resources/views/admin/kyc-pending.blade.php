@extends('layouts.app')
@section('title','KYC Approvals — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">KYC Verification Queue</h2>
      <p class="text-muted mb-4">Review seller identity and bank documents</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($kycs->count())
      <div style="display:flex;flex-direction:column;gap:1rem">
        @foreach($kycs as $kyc)
        <div class="card card-static card-body">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <div style="flex:1">
              <div style="font-weight:600;font-family:var(--fu)">{{ $kyc->user->name }}</div>
              <div style="font-size:.82rem;color:var(--ink-l);margin-top:.3rem">{{ $kyc->user->email }} · Submitted {{ $kyc->created_at->format('d M Y') }}</div>
              <div class="g-grid mt-3" style="grid-template-columns:repeat(3,1fr);gap:.5rem;font-size:.82rem;font-family:var(--fu)">
                <div><div class="text-muted">PAN</div><div class="fw-600">{{ $kyc->pan_number }}</div></div>
                <div><div class="text-muted">Aadhaar</div><div class="fw-600">{{ substr($kyc->aadhaar_number,0,4).'XXXXXXXX' }}</div></div>
                <div><div class="text-muted">Bank</div><div class="fw-600">{{ $kyc->bank_name }}</div></div>
                <div><div class="text-muted">Account</div><div class="fw-600">XXXXXX{{ substr($kyc->account_number,-4) }}</div></div>
                <div><div class="text-muted">IFSC</div><div class="fw-600">{{ $kyc->ifsc_code }}</div></div>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;flex-shrink:0">
              <form action="{{ route('admin.kyc.approve',$kyc) }}" method="POST">@csrf<button type="submit" class="btn btn-success btn-sm">✓ Approve KYC</button></form>
              <form action="{{ route('admin.kyc.reject',$kyc) }}" method="POST">@csrf
                <input type="text" name="reason" class="form-control" style="font-size:.82rem;margin-bottom:.4rem" placeholder="Rejection reason…" required>
                <button type="submit" class="btn btn-danger btn-sm w-full" style="justify-content:center">✗ Reject</button>
              </form>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div style="margin-top:1.5rem">{{ $kycs->links() }}</div>
      @else
      <div class="card card-static card-body text-center" style="padding:4rem"><div style="font-size:3rem;margin-bottom:1rem">✅</div><h3>No pending KYC requests</h3></div>
      @endif
    </main>
  </div>
</div>
@endsection
