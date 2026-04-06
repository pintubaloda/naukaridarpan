@extends('layouts.app')
@section('title','Manage Exams — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:end;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Manage Exams</h2>
          <p class="text-muted">View published, draft, rejected and pending exams with edit access.</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
          <a href="{{ route('admin.papers.create', ['input_type' => 'typed']) }}" class="btn btn-outline btn-sm">+ Manual Exam Entry</a>
          <a href="{{ route('admin.papers.create', ['input_type' => 'pdf']) }}" class="btn btn-primary btn-sm">+ Upload Paper</a>
          <a href="{{ route('admin.reports') }}" class="btn btn-ghost btn-sm">View Reports</a>
        </div>
      </div>

      <form action="{{ route('admin.exams.index') }}" method="GET" style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap">
        <input type="search" name="search" class="form-control" style="max-width:280px" placeholder="Search title, subject or slug…" value="{{ request('search') }}">
        <select name="status" class="form-control" style="max-width:180px" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          @foreach(['approved','draft','pending_review','rejected'] as $status)
            <option value="{{ $status }}" {{ request('status')===$status?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
      </form>

      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Exam</th>
              <th>Seller</th>
              <th>Status</th>
              <th>Parse</th>
              <th>Attempts</th>
              <th>Avg Score</th>
              <th>Risk</th>
              <th>Price</th>
              <th>Updated</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($papers as $paper)
            <tr>
              <td>
                <div style="font-weight:600;font-family:var(--fu)">{{ $paper->title }}</div>
                <div class="text-muted" style="font-size:.82rem;margin-top:.2rem">
                  {{ $paper->subject ?: 'No subject' }} · {{ $paper->category->name ?? 'Uncategorized' }} · {{ $paper->exam_type === 'previous_year' ? 'PYQ' : 'Mock' }}
                </div>
              </td>
              <td class="text-muted">{{ $paper->seller->name ?? 'Naukaridarpan' }}</td>
              <td><span class="badge {{ ['approved'=>'badge-green','draft'=>'badge-gray','pending_review'=>'badge-gold','rejected'=>'badge-red'][$paper->status] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$paper->status)) }}</span></td>
              <td><span class="badge {{ ['done'=>'badge-green','failed'=>'badge-red','processing'=>'badge-teal','pending'=>'badge-gold'][$paper->parse_status] ?? 'badge-gray' }}">{{ ucfirst($paper->parse_status) }}</span></td>
              <td>{{ number_format($paper->submitted_attempts_count ?? 0) }}</td>
              <td>{{ $paper->avg_score !== null ? number_format($paper->avg_score, 2).'%' : '—' }}</td>
              <td>
                @if(($paper->high_risk_attempts_count ?? 0) > 0)
                  <span class="badge badge-red">{{ $paper->high_risk_attempts_count }} alert(s)</span>
                @else
                  <span class="badge badge-green">Clean</span>
                @endif
              </td>
              <td>{{ $paper->is_free ? 'Free' : '₹'.number_format($paper->student_price,0) }}</td>
              <td class="text-muted">{{ $paper->updated_at->format('d M Y') }}</td>
              <td><a href="{{ route('admin.exams.edit',$paper) }}" class="btn btn-ghost btn-sm">Edit</a></td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted" style="padding:2rem">No exams found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div style="margin-top:1.5rem">{{ $papers->links() }}</div>
    </main>
  </div>
</div>
@endsection
