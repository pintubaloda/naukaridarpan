@extends('layouts.app')
@section('title','Admin Dashboard — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div style="display:flex;align-items:center;gap:.65rem">
          <div style="width:38px;height:38px;border-radius:50%;background:var(--teal);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem">AD</div>
          <div>
            <div style="font-weight:600;font-size:.88rem;font-family:var(--fu)">Admin Panel</div>
            <div style="font-size:.75rem;color:var(--ink-l)">Naukaridarpan</div>
          </div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>Dashboard</a>
        <div class="nav-sep">Content</div>
        <a href="{{ route('admin.exams.pending') }}" class="{{ request()->routeIs('admin.exams.*')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-width="2" stroke-linecap="round"/></svg>Exam Approvals @if($stats['pending_review']>0)<span class="badge badge-saffron" style="margin-left:auto">{{ $stats['pending_review'] }}</span>@endif</a>
        <a href="{{ route('admin.scraped') }}" class="{{ request()->routeIs('admin.scraped')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="4" y1="22" x2="4" y2="15" stroke-width="2" stroke-linecap="round"/></svg>Scraped Papers</a>
        <a href="{{ route('admin.blog.index') }}" class="{{ request()->routeIs('admin.blog.*')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" stroke-width="2" stroke-linecap="round"/></svg>Blog Manager</a>
        <div class="nav-sep">Users</div>
        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-width="2" stroke-linecap="round"/></svg>All Users</a>
        <a href="{{ route('admin.kyc.pending') }}" class="{{ request()->routeIs('admin.kyc.*')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke-width="2"/></svg>KYC Requests @if($stats['pending_kyc']>0)<span class="badge badge-saffron" style="margin-left:auto">{{ $stats['pending_kyc'] }}</span>@endif</a>
        <a href="{{ route('admin.payouts') }}" class="{{ request()->routeIs('admin.payouts')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-8v1m0 9v1" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>Payouts @if($stats['pending_payout']>0)<span class="badge badge-saffron" style="margin-left:auto">{{ $stats['pending_payout'] }}</span>@endif</a>
        <a href="{{ route('admin.professor-leads') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2" stroke-linecap="round"/></svg>Professor Leads</a>
        <div class="nav-sep">System</div>
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>Settings</a>
      </nav>
    </aside>

    <main>
      <h2 class="mb-4">Platform Overview</h2>
      <div class="g-grid" style="grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem">
        @foreach([
          ['icon-teal','👥','Total Users',number_format($stats['total_users'])],
          ['icon-saffron','📄','Exam Papers',number_format($stats['total_papers'])],
          ['icon-green','🛒','Total Sales',number_format($stats['total_sales'])],
          ['icon-gold','💰','Revenue',  '₹'.number_format($stats['total_revenue'],0)],
        ] as [$ic,$em,$lbl,$val])
        <div class="stat-card">
          <div class="stat-icon {{ $ic }}">{{ $em }}</div>
          <div class="stat-label">{{ $lbl }}</div>
          <div class="stat-val">{{ $val }}</div>
        </div>
        @endforeach
      </div>

      {{-- Action items --}}
      @if($stats['pending_review'] || $stats['pending_kyc'] || $stats['pending_payout'])
      <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem">
        @if($stats['pending_review'])<a href="{{ route('admin.exams.pending') }}" style="text-decoration:none"><div class="card card-static card-body text-center" style="border:1.5px solid var(--saffron);background:var(--saffron-l)"><div style="font-size:1.5rem;margin-bottom:.25rem">📋</div><div style="font-family:var(--fd);font-size:1.5rem;color:var(--saffron)">{{ $stats['pending_review'] }}</div><div style="font-size:.82rem;color:var(--saffron-d);font-family:var(--fu)">Exams Awaiting Approval</div></div></a>@endif
        @if($stats['pending_kyc'])<a href="{{ route('admin.kyc.pending') }}" style="text-decoration:none"><div class="card card-static card-body text-center" style="border:1.5px solid var(--teal);background:var(--teal-l)"><div style="font-size:1.5rem;margin-bottom:.25rem">🔐</div><div style="font-family:var(--fd);font-size:1.5rem;color:var(--teal)">{{ $stats['pending_kyc'] }}</div><div style="font-size:.82rem;color:var(--teal-d);font-family:var(--fu)">KYC Verifications Pending</div></div></a>@endif
        @if($stats['pending_payout'])<a href="{{ route('admin.payouts') }}" style="text-decoration:none"><div class="card card-static card-body text-center" style="border:1.5px solid var(--gold);background:var(--gold-l)"><div style="font-size:1.5rem;margin-bottom:.25rem">💳</div><div style="font-family:var(--fd);font-size:1.5rem;color:#7A5C10">{{ $stats['pending_payout'] }}</div><div style="font-size:.82rem;color:#7A5C10;font-family:var(--fu)">Payout Requests Pending</div></div></a>@endif
      </div>
      @endif

      {{-- Recent sales --}}
      <div class="card card-static">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Recent Sales</h3></div>
        <div class="tbl-wrap" style="border:none;border-radius:0">
          <table class="tbl">
            <thead><tr><th>Student</th><th>Exam Paper</th><th>Seller</th><th>Amount</th><th>Commission</th><th>Date</th></tr></thead>
            <tbody>
              @forelse($recentSales as $s)
              <tr>
                <td style="font-weight:500;font-family:var(--fu)">{{ $s->student->name }}</td>
                <td class="text-muted">{{ Str::limit($s->examPaper->title,40) }}</td>
                <td class="text-muted">{{ $s->examPaper->seller->name }}</td>
                <td>₹{{ number_format($s->amount_paid,0) }}</td>
                <td class="text-ok fw-600">₹{{ number_format($s->platform_commission,0) }}</td>
                <td class="text-muted">{{ $s->created_at->format('d M') }}</td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-muted text-center" style="padding:2rem">No sales yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
