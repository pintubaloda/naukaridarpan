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

      <div class="g-grid" style="grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem">
        <a href="{{ route('admin.exams.index') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📚</div>
            <div style="font-weight:600;font-family:var(--fu)">Manage Exams</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Open all exams, sync to TAO, and review statuses.</div>
          </div>
        </a>
        <a href="{{ route('admin.exams.pending') }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">📝</div>
            <div style="font-weight:600;font-family:var(--fu)">Exam Approvals</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Approve pending exams and trigger TAO sync on approval.</div>
          </div>
        </a>
        <a href="{{ route('admin.papers.create', ['input_type' => 'typed']) }}" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">✍️</div>
            <div style="font-weight:600;font-family:var(--fu)">Manual Exam Entry</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Create typed papers directly from admin.</div>
          </div>
        </a>
        @if(config('services.tao.url'))
        <a href="{{ config('services.tao.url') }}" target="_blank" rel="noopener" style="text-decoration:none">
          <div class="card card-static card-body" style="height:100%">
            <div style="font-size:1.25rem;margin-bottom:.5rem">🔗</div>
            <div style="font-weight:600;font-family:var(--fu)">Open TAO</div>
            <div class="text-muted" style="font-size:.82rem;margin-top:.35rem">Jump straight into the TAO instance for advanced exam work.</div>
          </div>
        </a>
        @endif
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
