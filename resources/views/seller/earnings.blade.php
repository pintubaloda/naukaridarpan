@extends('layouts.app')
@section('title','Earnings — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <h2 class="mb-1">Earnings & Settlements</h2>
      <p class="text-muted mb-4">Detailed breakdown of every sale and credit</p>
      <div class="g-grid grid-3 mb-4">
        <div class="stat-card"><div class="stat-icon icon-green">💳</div><div class="stat-label">Wallet Balance</div><div class="stat-val text-ok">₹{{ number_format($profile?->wallet_balance??0,0) }}</div></div>
        <div class="stat-card"><div class="stat-icon icon-gold">⏳</div><div class="stat-label">Pending 48hr</div><div class="stat-val">₹{{ number_format($profile?->pending_balance??0,0) }}</div></div>
        <div class="stat-card"><div class="stat-icon icon-teal">🏆</div><div class="stat-label">Total Earned</div><div class="stat-val">₹{{ number_format($profile?->total_earnings??0,0) }}</div></div>
      </div>
      <div class="card card-static">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Sale-by-Sale Breakdown</div>
        @if($settlements->count())
        <div class="tbl-wrap" style="border:none;border-radius:0">
          <table class="tbl">
            <thead><tr><th>Date</th><th>Paper</th><th>Student Paid</th><th>Platform Fee</th><th>Your Credit</th><th>Settlement</th></tr></thead>
            <tbody>
              @foreach($settlements as $s)
              <tr>
                <td class="text-muted" style="white-space:nowrap">{{ $s->created_at->format('d M Y') }}</td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:var(--fu);font-size:.85rem">{{ $s->examPaper->title }}</td>
                <td>₹{{ number_format($s->amount_paid,0) }}</td>
                <td class="text-muted">₹{{ number_format($s->platform_commission,0) }}</td>
                <td class="fw-600 text-ok">₹{{ number_format($s->seller_credit,0) }}</td>
                <td><span class="badge {{ $s->is_settled?'badge-green':'badge-gold' }}">{{ $s->is_settled?'Settled':'Pending' }}</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="padding:1rem">{{ $settlements->links() }}</div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--ink-l)"><p>No sales yet. <a href="{{ route('seller.papers.create') }}">Upload your first paper.</a></p></div>
        @endif
      </div>
    </main>
  </div>
</div>
@endsection
