@extends('layouts.app')
@section('title','Exam Approvals — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">Pending Exam Approvals</h2>
      <p class="text-muted mb-4">Review and approve seller-submitted exam papers</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($papers->count())
      <div style="display:flex;flex-direction:column;gap:1rem">
        @foreach($papers as $paper)
        <div class="card card-static card-body">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-family:var(--fu);font-size:.95rem">{{ $paper->title }}</div>
              <div style="font-size:.82rem;color:var(--ink-l);margin-top:.3rem">
                {{ $paper->category->name }} · {{ $paper->seller->name }} · {{ $paper->total_questions }} Qs · {{ $paper->duration_minutes }} min · {{ $paper->is_free?'Free':'₹'.number_format($paper->student_price,0) }}
              </div>
              <div style="margin-top:.5rem;display:flex;gap:.4rem;flex-wrap:wrap">
                <span class="badge badge-gray">{{ ucfirst($paper->difficulty) }}</span>
                <span class="badge badge-gray">{{ $paper->language }}</span>
                <span class="badge {{ ['done'=>'badge-green','failed'=>'badge-red','processing'=>'badge-teal','pending'=>'badge-gold'][$paper->parse_status]??'badge-gray' }}">Parse: {{ ucfirst($paper->parse_status) }}</span>
                <span class="badge {{ ['synced'=>'badge-green','failed'=>'badge-red','pending'=>'badge-gold'][$paper->tao_sync_status ?? 'pending'] ?? 'badge-gray' }}">TAO: {{ ucfirst($paper->tao_sync_status ?? 'pending') }}</span>
              </div>
              @if($paper->description)<p style="font-size:.85rem;color:var(--ink-m);margin-top:.5rem">{{ Str::limit($paper->description,200) }}</p>@endif
            </div>
            <div style="display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;flex-shrink:0">
              <form action="{{ route('admin.exams.approve',$paper) }}" method="POST">@csrf<button type="submit" class="btn btn-success btn-sm">✓ Approve</button></form>
              <div x-data="{ open:false }">
                <form action="{{ route('admin.exams.reject',$paper) }}" method="POST">@csrf
                  <input type="text" name="reason" class="form-control" style="font-size:.82rem;margin-bottom:.4rem" placeholder="Rejection reason…" required>
                  <button type="submit" class="btn btn-danger btn-sm w-full" style="justify-content:center">✗ Reject</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div style="margin-top:1.5rem">{{ $papers->links() }}</div>
      @else
      <div class="card card-static card-body text-center" style="padding:4rem"><div style="font-size:3rem;margin-bottom:1rem">✅</div><h3>All caught up!</h3><p class="mt-2 text-muted">No papers waiting for approval.</p></div>
      @endif
    </main>
  </div>
</div>
@endsection
