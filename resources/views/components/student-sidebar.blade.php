<aside class="sidebar">
  <div class="sidebar-header">
    <div style="display:flex;align-items:center;gap:.65rem">
      <div style="width:38px;height:38px;border-radius:50%;background:var(--teal);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
      <div><div style="font-weight:600;font-size:.88rem;font-family:var(--fu)">{{ Str::limit(auth()->user()->name,20) }}</div><div style="font-size:.75rem;color:var(--ink-l)">Student</div></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>Dashboard</a>
    <a href="{{ route('student.exams') }}" class="{{ request()->routeIs('student.exams')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-width="2" stroke-linecap="round"/></svg>My Exams</a>
    <a href="{{ route('student.results') }}" class="{{ request()->routeIs('student.results')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M16 8v8m-8-5v5m-4 0h16M4 3h16" stroke-width="2" stroke-linecap="round"/></svg>Results</a>
    <a href="{{ route('exams.browse') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/></svg>Browse Exams</a>
    <div class="nav-sep">Account</div>
    <a href="{{ route('student.profile') }}" class="{{ request()->routeIs('student.profile')?'active':'' }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke-width="2" stroke-linecap="round"/></svg>My Profile</a>
  </nav>
</aside>
