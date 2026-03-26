@extends('layouts.app')
@section('title','Seller Dashboard — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
      <div class="sidebar-header">
        <div style="display:flex;align-items:center;gap:.65rem">
          <div style="width:38px;height:38px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
          <div>
            <div style="font-weight:600;font-size:.88rem;font-family:var(--fu)">{{ Str::limit(auth()->user()->name,20) }}</div>
            <div style="font-size:.75rem;color:var(--ink-l)">Seller Account</div>
          </div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="{{ route('seller.dashboard') }}" class="{{ request()->routeIs('seller.dashboard')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
          Dashboard
        </a>
        <a href="{{ route('seller.papers') }}" class="{{ request()->routeIs('seller.papers*')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-width="2" stroke-linecap="round"/></svg>
          My Papers
        </a>
        <a href="{{ route('seller.papers.create') }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke-width="2" stroke-linecap="round"/></svg>
          Upload New Paper
        </a>
        <a href="{{ route('seller.analytics') }}" class="{{ request()->routeIs('seller.analytics')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 17l4-8 4 4 4-6 4 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Analytics
        </a>
        <div class="nav-sep">Account</div>
        <a href="{{ route('seller.earnings') }}" class="{{ request()->routeIs('seller.earnings')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-8v1m0 9v1" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
          Earnings
        </a>
        <a href="{{ route('seller.payouts') }}" class="{{ request()->routeIs('seller.payouts')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h12" stroke-width="2" stroke-linecap="round"/></svg>
          Payouts
        </a>
        <a href="{{ route('seller.kyc') }}" class="{{ request()->routeIs('seller.kyc')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke-width="2"/></svg>
          KYC Verification
        </a>
        <a href="{{ route('seller.profile') }}" class="{{ request()->routeIs('seller.profile')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke-width="2" stroke-linecap="round"/></svg>
          My Profile
        </a>
      </nav>
    </aside>

    {{-- MAIN --}}
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
          <h2 class="mb-1">Welcome back, {{ Str::words(auth()->user()->name,1,'') }}!</h2>
          <p style="font-size:.9rem">Here's your seller overview</p>
        </div>
        <a href="{{ route('seller.papers.create') }}" class="btn btn-primary">+ Upload New Paper</a>
      </div>

      {{-- KYC alert --}}
      @php $kyc = auth()->user()->kyc; @endphp
      @if(!$kyc || $kyc->status !== 'approved')
      <div class="alert alert-warning mb-3">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke-width="2" stroke-linecap="round"/></svg>
        <div>
          <strong>KYC verification required</strong> to request payouts.
          <a href="{{ route('seller.kyc') }}" class="fw-600">Complete KYC now →</a>
          @if($kyc) Current status: <strong>{{ ucfirst($kyc->status) }}</strong>@endif
        </div>
      </div>
      @endif

      {{-- Stats --}}
      <div class="g-grid grid-4 mb-4">
        <div class="stat-card">
          <div class="stat-icon icon-teal">💰</div>
          <div class="stat-label">Wallet Balance</div>
          <div class="stat-val text-teal">₹{{ number_format($profile?->wallet_balance ?? 0,0) }}</div>
          <div class="stat-sub">₹{{ number_format($profile?->pending_balance ?? 0,0) }} pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-saffron">📄</div>
          <div class="stat-label">Total Papers</div>
          <div class="stat-val">{{ $paperCount }}</div>
          <div class="stat-sub">{{ $approvedCount }} approved · {{ $pendingCount }} pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-green">🛒</div>
          <div class="stat-label">Total Sales</div>
          <div class="stat-val">{{ number_format($profile?->total_sales ?? 0) }}</div>
          <div class="stat-sub">All time purchases</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-gold">⭐</div>
          <div class="stat-label">Rating</div>
          <div class="stat-val">{{ number_format($profile?->rating ?? 0,1) }}</div>
          <div class="stat-sub">{{ $profile?->total_reviews ?? 0 }} reviews</div>
        </div>
      </div>

      {{-- Recent sales --}}
      <div class="card card-static">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-size:1rem">Recent Sales</h3>
          <a href="{{ route('seller.earnings') }}" class="text-saffron" style="font-size:.85rem">View All →</a>
        </div>
        @if($recentSales->count())
        <div class="tbl-wrap" style="border:none;border-radius:0">
          <table class="tbl">
            <thead><tr><th>Student</th><th>Exam Paper</th><th>Amount</th><th>Your Credit</th><th>Date</th></tr></thead>
            <tbody>
              @foreach($recentSales as $s)
              <tr>
                <td style="font-weight:500;font-family:var(--fu)">{{ $s->student->name }}</td>
                <td style="color:var(--ink-m)">{{ Str::limit($s->examPaper->title,45) }}</td>
                <td>₹{{ number_format($s->amount_paid,0) }}</td>
                <td><span class="text-ok fw-600">₹{{ number_format($s->seller_credit,0) }}</span></td>
                <td class="text-muted">{{ $s->created_at->format('d M Y') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--ink-l)">
          <div style="font-size:2.5rem;margin-bottom:.75rem">📈</div>
          <p>No sales yet. <a href="{{ route('seller.papers.create') }}">Upload your first exam paper</a> to start earning.</p>
        </div>
        @endif
      </div>
    </main>
  </div>
</div>
@endsection
