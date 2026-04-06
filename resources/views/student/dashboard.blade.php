@extends('layouts.app')
@section('title','My Dashboard — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div style="display:flex;align-items:center;gap:.65rem">
          <div style="width:38px;height:38px;border-radius:50%;background:var(--teal);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
          <div>
            <div style="font-weight:600;font-size:.88rem;font-family:var(--fu)">{{ Str::limit(auth()->user()->name,20) }}</div>
            <div style="font-size:.75rem;color:var(--ink-l)">Student Account</div>
          </div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
          Dashboard
        </a>
        <a href="{{ route('student.exams') }}" class="{{ request()->routeIs('student.exams')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-width="2" stroke-linecap="round"/></svg>
          My Exams
        </a>
        <a href="{{ route('student.results') }}" class="{{ request()->routeIs('student.results')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 8v8m-8-5v5m-4 0h16M4 3h16" stroke-width="2" stroke-linecap="round"/></svg>
          My Results
        </a>
        <a href="{{ route('exams.browse') }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/></svg>
          Browse Exams
        </a>
        <div class="nav-sep">Account</div>
        <a href="{{ route('student.profile') }}" class="{{ request()->routeIs('student.profile')?'active':'' }}">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke-width="2" stroke-linecap="round"/></svg>
          My Profile
        </a>
      </nav>
    </aside>

    <main>
      <h2 class="mb-1">Hello, {{ Str::words(auth()->user()->name,1,'') }}! 👋</h2>
      <p class="mb-4">Ready to crack your exam today?</p>

      {{-- Stats --}}
      <div class="g-grid grid-4 mb-4">
        @foreach([
          ['icon-teal','📚','Exams Purchased',$totalPurchases,'Keep practising!'],
          ['icon-saffron','✍️','Attempts Made',$totalAttempts,'Great effort!'],
          ['icon-green','📊','Avg. Score',($avgScore?round($avgScore,1).'%':'N/A'),''],
          ['icon-gold','🎯','Streak','Active','Keep it up!'],
        ] as [$ic,$emoji,$lbl,$val,$sub])
        <div class="stat-card">
          <div class="stat-icon {{ $ic }}">{{ $emoji }}</div>
          <div class="stat-label">{{ $lbl }}</div>
          <div class="stat-val">{{ $val }}</div>
          <div class="stat-sub">{{ $sub }}</div>
        </div>
        @endforeach
      </div>

      {{-- Recent exams --}}
      <div class="card card-static mb-4">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-size:1rem">Continue Practising</h3>
          <a href="{{ route('student.exams') }}" class="text-saffron" style="font-size:.85rem">All My Exams →</a>
        </div>
        @if($recentPurchases->count())
        <div style="display:flex;flex-direction:column;gap:0">
          @foreach($recentPurchases as $p)
          @php $activeAttempt = $p->attempts->where('status', 'in_progress')->sortByDesc('created_at')->first(); @endphp
          <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);gap:1rem;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0">
              <div style="width:40px;height:40px;border-radius:var(--r2);background:var(--teal-l);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">📝</div>
              <div style="min-width:0">
                <div style="font-weight:600;font-size:.9rem;font-family:var(--fu);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->examPaper->title }}</div>
                <div style="font-size:.78rem;color:var(--ink-l)">{{ $p->examPaper->category->name ?? '' }} · {{ $p->retakes_used }}/{{ $p->retakes_allowed }} attempts used</div>
              </div>
            </div>
            @if($activeAttempt)
            <a href="{{ route('student.exam.take',$p) }}" class="btn btn-primary btn-sm" style="flex-shrink:0">Resume →</a>
            @elseif($p->canAttempt())
            <a href="{{ route('student.exam.start',$p) }}" class="btn btn-primary btn-sm" style="flex-shrink:0">Start →</a>
            @else
            <span class="badge badge-gray">No retakes left</span>
            @endif
          </div>
          @endforeach
        </div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--ink-l)">
          <div style="font-size:2.5rem;margin-bottom:.75rem">📚</div>
          <p style="margin-bottom:1rem">You haven't purchased any exams yet.</p>
          <a href="{{ route('exams.browse') }}" class="btn btn-primary">Browse Exams</a>
        </div>
        @endif
      </div>

      <div class="cta-banner" style="background:var(--teal)">
        <div>
          <h3 style="color:#fff;font-size:1.25rem">Explore 500+ Free PYQ Papers</h3>
          <p style="color:rgba(255,255,255,.75);font-size:.9rem;margin-top:.25rem">Official previous year question papers from UPSC, SSC, Railway and more — completely free.</p>
        </div>
        <a href="{{ route('exams.browse',['price'=>'free']) }}" class="btn btn-white">Browse Free Papers</a>
      </div>
    </main>
  </div>
</div>
@endsection
