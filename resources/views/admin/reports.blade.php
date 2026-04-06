@extends('layouts.app')
@section('title','Reports — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')

    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Reports</h2>
          <p class="text-muted" style="margin:0">A single place to check attempt volume, integrity alerts, weak areas, and top performers in the Laravel-native engine.</p>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
        <div class="card card-static card-body">
          <div class="text-muted" style="font-size:.8rem">Submitted Attempts</div>
          <div style="font-size:1.75rem;font-weight:700;color:var(--teal)">{{ number_format($submittedAttempts->count()) }}</div>
        </div>
        <div class="card card-static card-body">
          <div class="text-muted" style="font-size:.8rem">High Risk</div>
          <div style="font-size:1.75rem;font-weight:700;color:var(--err)">{{ number_format($riskBreakdown['high']) }}</div>
        </div>
        <div class="card card-static card-body">
          <div class="text-muted" style="font-size:.8rem">Medium Risk</div>
          <div style="font-size:1.75rem;font-weight:700;color:var(--gold)">{{ number_format($riskBreakdown['medium']) }}</div>
        </div>
        <div class="card card-static card-body">
          <div class="text-muted" style="font-size:.8rem">Low Risk</div>
          <div style="font-size:1.75rem;font-weight:700;color:var(--ok)">{{ number_format($riskBreakdown['low']) }}</div>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:1.1fr .9fr;gap:1rem;margin-bottom:1.5rem">
        <div class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Weak Areas Across Attempts</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Area</th><th>Average Accuracy</th><th>Samples</th></tr></thead>
              <tbody>
                @forelse($weakAreas as $area)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ $area['label'] }}</td>
                  <td>{{ number_format($area['avg_accuracy'], 2) }}%</td>
                  <td class="text-muted">{{ number_format($area['attempts']) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center" style="padding:2rem">Not enough submitted attempts yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Most Attempted Exams</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Exam</th><th>Attempts</th></tr></thead>
              <tbody>
                @forelse($topExams as $exam)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ \Illuminate\Support\Str::limit($exam->title, 42) }}</td>
                  <td>{{ number_format($exam->submitted_attempts_count ?? 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-muted text-center" style="padding:2rem">No exam attempt data yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:1rem">
        <div class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Top Students</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Student</th><th>Exam</th><th>Score</th><th>Time</th></tr></thead>
              <tbody>
                @forelse($topStudents as $attempt)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ $attempt->student->name ?? 'Student' }}</td>
                  <td class="text-muted">{{ \Illuminate\Support\Str::limit($attempt->examPaper->title ?? 'Exam', 28) }}</td>
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
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Recent Integrity Flags</h3></div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead><tr><th>Student</th><th>Exam</th><th>Risk</th><th>Submitted</th></tr></thead>
              <tbody>
                @forelse($submittedAttempts->filter(fn($attempt) => in_array($attempt->anti_cheat_review['risk_level'] ?? 'low', ['high','medium'], true))->take(10) as $attempt)
                <tr>
                  <td style="font-weight:500;font-family:var(--fu)">{{ $attempt->student->name ?? 'Student' }}</td>
                  <td class="text-muted">{{ \Illuminate\Support\Str::limit($attempt->examPaper->title ?? 'Exam', 28) }}</td>
                  <td><span class="badge {{ ($attempt->anti_cheat_review['risk_level'] ?? 'low') === 'high' ? 'badge-red' : 'badge-gold' }}">{{ ucfirst($attempt->anti_cheat_review['risk_level'] ?? 'low') }}</span></td>
                  <td class="text-muted">{{ $attempt->submitted_at?->format('d M, h:i A') ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center" style="padding:2rem">No medium/high risk attempts yet.</td></tr>
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
