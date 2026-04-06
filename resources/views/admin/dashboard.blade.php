@extends('layouts.app')
@section('title','Admin Dashboard — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')

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

      <div class="g-grid" style="grid-template-columns:repeat(2,1fr);gap:1rem;margin-bottom:2rem">
        <div class="card card-static card-body">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem">
            <div>
              <div class="text-muted" style="font-size:.8rem">Submitted Attempts</div>
              <div style="font-size:1.75rem;font-weight:700;color:var(--teal)">{{ number_format($stats['submitted_attempts']) }}</div>
            </div>
            <div style="text-align:right">
              <div class="text-muted" style="font-size:.8rem">High Risk Attempts</div>
              <div style="font-size:1.75rem;font-weight:700;color:var(--err)">{{ number_format($stats['high_risk_attempts']) }}</div>
            </div>
          </div>
        </div>
        <div class="card card-static card-body">
          <div style="font-weight:600;font-family:var(--fu);margin-bottom:.35rem">Engine Status</div>
          <div class="text-muted" style="font-size:.84rem">Autosave, resume, analytics, section timers, ranking, and integrity review are now flowing through the Laravel-native exam engine.</div>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem">
        <a href="{{ route('admin.exams.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📚</div>
            <div style="font-weight:600;font-family:var(--fu)">Manage Exams</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Open all exams, review statuses, and maintain the Laravel-native engine.</div>
          </div>
        </a>
        <a href="{{ route('admin.question-bank.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">🧩</div>
            <div style="font-weight:600;font-family:var(--fu)">Question Bank</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Create reusable questions for multiple exams and sections.</div>
          </div>
        </a>
        <a href="{{ route('admin.exam-templates.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📐</div>
            <div style="font-weight:600;font-family:var(--fu)">Exam Templates</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Save standard exam blueprints and section layouts.</div>
          </div>
        </a>
        <a href="{{ route('admin.exams.pending') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📝</div>
            <div style="font-weight:600;font-family:var(--fu)">Exam Approvals</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Approve pending exams once content quality and readiness checks pass.</div>
          </div>
        </a>
        <a href="{{ route('admin.papers.create', ['input_type' => 'typed']) }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">✍️</div>
            <div style="font-weight:600;font-family:var(--fu)">Manual Exam Entry</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Create typed papers directly from admin.</div>
          </div>
        </a>
        <a href="{{ route('admin.reports') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📊</div>
            <div style="font-weight:600;font-family:var(--fu)">Reports</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Track attempts, weak areas, flagged exams, and top performers.</div>
          </div>
        </a>
        <a href="{{ route('admin.qti.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📦</div>
            <div style="font-weight:600;font-family:var(--fu)">QTI Packages</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Import and export QTI-compatible packages from the exam engine.</div>
          </div>
        </a>
        <a href="{{ route('admin.interoperability.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">🔌</div>
            <div style="font-weight:600;font-family:var(--fu)">Interoperability</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Manage external assessment endpoints and preview exchange payloads.</div>
          </div>
        </a>
        <a href="{{ route('admin.automation-sources.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">🛰️</div>
            <div style="font-weight:600;font-family:var(--fu)">Automation Sources</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Review n8n-discovered RSS feeds, site sources, and sync runs for blogs and professor leads.</div>
          </div>
        </a>
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

      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:1rem;margin-top:2rem">
        <div class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Top Performing Attempts</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Student</th><th>Exam</th><th>Score</th><th>Time</th></tr></thead>
              <tbody>
                @forelse($topAttempts as $attempt)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ $attempt->student->name ?? 'Student' }}</td>
                  <td class="text-muted">{{ Str::limit($attempt->examPaper->title ?? 'Exam', 32) }}</td>
                  <td>{{ number_format($attempt->percentage ?? 0, 2) }}%</td>
                  <td class="text-muted">{{ gmdate('i:s', $attempt->time_taken_seconds ?? 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center" style="padding:2rem">No submitted attempts yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">High Risk Integrity Alerts</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Student</th><th>Exam</th><th>Risk</th><th>Submitted</th></tr></thead>
              <tbody>
                @forelse($highRiskAttempts as $attempt)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ $attempt->student->name ?? 'Student' }}</td>
                  <td class="text-muted">{{ Str::limit($attempt->examPaper->title ?? 'Exam', 32) }}</td>
                  <td><span class="badge badge-red">{{ ucfirst($attempt->anti_cheat_review['risk_level'] ?? 'high') }}</span></td>
                  <td class="text-muted">{{ $attempt->submitted_at?->format('d M, h:i A') ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center" style="padding:2rem">No high-risk attempts flagged yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
