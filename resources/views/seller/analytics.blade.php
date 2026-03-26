@extends('layouts.app')
@section('title','Analytics — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <h2 class="mb-1">Analytics</h2>
      <p class="text-muted mb-4">Sales performance overview</p>
      <div class="card card-static mb-4">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Monthly Revenue (Last 12 Months)</div>
        <div class="card-body">
          @if($monthlySales->count())
          <div style="overflow-x:auto">
            <table class="tbl" style="min-width:600px">
              <thead><tr><th>Month</th><th>Sales Count</th><th>Revenue</th></tr></thead>
              <tbody>
                @foreach($monthlySales as $m)
                <tr>
                  <td class="fw-600">{{ \Carbon\Carbon::parse($m->month.'-01')->format('M Y') }}</td>
                  <td>{{ $m->count }} sales</td>
                  <td class="text-ok fw-600">₹{{ number_format($m->revenue,0) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <p class="text-muted text-center" style="padding:2rem 0">No sales data yet.</p>
          @endif
        </div>
      </div>
      <div class="card card-static">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Top Performing Papers</div>
        @if($topPapers->count())
        <div class="tbl-wrap" style="border:none;border-radius:0">
          <table class="tbl">
            <thead><tr><th>Paper</th><th>Purchases</th><th>Avg Score</th></tr></thead>
            <tbody>
              @foreach($topPapers as $p)
              <tr>
                <td style="font-family:var(--fu);font-size:.88rem">{{ Str::limit($p->title,50) }}</td>
                <td class="fw-600">{{ $p->total_purchases }}</td>
                <td>{{ $p->avg_score>0 ? round($p->avg_score).'%' : '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--ink-l)"><p>No approved papers yet.</p></div>
        @endif
      </div>
    </main>
  </div>
</div>
@endsection
