<aside class="sidebar">
  <div class="sidebar-header">
    <div style="display:flex;align-items:center;gap:.65rem">
      <div style="width:38px;height:38px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
      <div><div style="font-weight:600;font-size:.88rem;font-family:var(--fu)">{{ Str::limit(auth()->user()->name,20) }}</div><div style="font-size:.75rem;color:var(--ink-l)">Seller</div></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a href="{{ route('seller.dashboard') }}" class="{{ request()->routeIs('seller.dashboard')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>Dashboard</a>
    <a href="{{ route('seller.papers') }}" class="{{ request()->routeIs('seller.papers*')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-width="2" stroke-linecap="round"/></svg>My Papers</a>
    <a href="{{ route('seller.papers.create') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke-width="2" stroke-linecap="round"/></svg>Upload Paper</a>
    <a href="{{ route('seller.analytics') }}" class="{{ request()->routeIs('seller.analytics')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M3 17l4-8 4 4 4-6 4 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Analytics</a>
    <div class="nav-sep">Earnings</div>
    <a href="{{ route('seller.earnings') }}" class="{{ request()->routeIs('seller.earnings')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-8v1m0 9v1" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>Earnings</a>
    <a href="{{ route('seller.payouts') }}" class="{{ request()->routeIs('seller.payouts')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M3 6h18M3 12h18M3 18h12" stroke-width="2" stroke-linecap="round"/></svg>Payouts</a>
    <a href="{{ route('seller.kyc') }}" class="{{ request()->routeIs('seller.kyc')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round"/></svg>KYC</a>
    <a href="{{ route('seller.profile') }}" class="{{ request()->routeIs('seller.profile')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke-width="2" stroke-linecap="round"/></svg>Profile</a>
  </nav>
</aside>
