@extends('layouts.app')
@section('title','Payouts — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <h2 class="mb-1">Payout Requests</h2>
      <p class="text-muted mb-4">Minimum payout: ₹{{ number_format($threshold,0) }} · Settled to KYC-verified bank only</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-error mb-3">{{ session('error') }}</div>@endif

      {{-- Wallet card --}}
      <div class="g-grid grid-3 mb-4">
        <div class="stat-card"><div class="stat-icon icon-green">💰</div><div class="stat-label">Available Balance</div><div class="stat-val text-ok">₹{{ number_format($profile?->wallet_balance??0,0) }}</div><div class="stat-sub">Ready to withdraw</div></div>
        <div class="stat-card"><div class="stat-icon icon-gold">⏳</div><div class="stat-label">Pending (48hr hold)</div><div class="stat-val">₹{{ number_format($profile?->pending_balance??0,0) }}</div><div class="stat-sub">Released after 48hrs</div></div>
        <div class="stat-card"><div class="stat-icon icon-teal">🏦</div><div class="stat-label">Total Earned</div><div class="stat-val">₹{{ number_format($profile?->total_earnings??0,0) }}</div><div class="stat-sub">All time</div></div>
      </div>

      {{-- Request payout --}}
      @if($kyc?->status==='approved' && ($profile?->wallet_balance??0) >= $threshold)
      <div class="card card-static card-body mb-4" style="max-width:480px">
        <h3 style="font-size:1rem;margin-bottom:1rem">Request Payout</h3>
        <form action="{{ route('seller.payouts.request') }}" method="POST" style="display:flex;gap:.75rem;align-items:flex-end">@csrf
          <div class="form-group" style="margin:0;flex:1"><label class="form-label">Amount (₹)</label><input type="number" name="amount" class="form-control" placeholder="Min ₹{{ $threshold }}" min="{{ $threshold }}" max="{{ $profile?->wallet_balance??0 }}" step="1" required></div>
          <button type="submit" class="btn btn-primary">Request →</button>
        </form>
        <div class="form-hint mt-1">Payout to: {{ $kyc->bank_name }} — XXXXXX{{ substr($kyc->account_number,-4) }}</div>
      </div>
      @elseif(!$kyc || $kyc->status!=='approved')
        <div class="alert alert-warning mb-4">Complete <a href="{{ route('seller.kyc') }}" class="fw-600">KYC verification</a> to request payouts.</div>
      @else
        <div class="alert alert-info mb-4">Minimum balance of ₹{{ number_format($threshold,0) }} required to request payout. Current: ₹{{ number_format($profile?->wallet_balance??0,0) }}</div>
      @endif

      {{-- Payout history --}}
      <div class="card card-static">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Payout History</div>
        @if($payouts->count())
        <div class="tbl-wrap" style="border:none;border-radius:0">
          <table class="tbl">
            <thead><tr><th>Date</th><th>Amount</th><th>Bank</th><th>UTR</th><th>Status</th></tr></thead>
            <tbody>
              @foreach($payouts as $p)
              <tr>
                <td class="text-muted" style="white-space:nowrap">{{ $p->created_at->format('d M Y') }}</td>
                <td class="fw-600">₹{{ number_format($p->amount,0) }}</td>
                <td class="text-muted">{{ $p->bank_name }}</td>
                <td class="text-muted">{{ $p->utr_number ?? '—' }}</td>
                <td><span class="badge {{ ['pending'=>'badge-gold','processing'=>'badge-teal','paid'=>'badge-green','failed'=>'badge-red','rejected'=>'badge-red'][$p->status]??'badge-gray' }}">{{ ucfirst($p->status) }}</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="padding:1rem">{{ $payouts->links() }}</div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--ink-l)"><p>No payout requests yet.</p></div>
        @endif
      </div>
    </main>
  </div>
</div>
@endsection
