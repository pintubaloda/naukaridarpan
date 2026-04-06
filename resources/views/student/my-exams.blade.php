@extends('layouts.app')
@section('title','My Exams — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.student-sidebar')
    <main>
      <h2 class="mb-1">My Exam Library</h2>
      <p class="text-muted mb-4">All purchased exam papers</p>
      @if($purchases->count())
        <div style="display:flex;flex-direction:column;gap:1rem">
          @foreach($purchases as $p)
          @php
            $activeAttempt = $p->attempts->where('status', 'in_progress')->sortByDesc('created_at')->first();
            $latestAttempt = $p->attempts->sortByDesc('created_at')->first();
          @endphp
          <div class="card card-static" style="display:flex;align-items:center;gap:1.25rem;padding:1.25rem;flex-wrap:wrap">
            <div style="width:56px;height:56px;background:var(--teal-l);border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">📝</div>
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-family:var(--fu);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->examPaper->title }}</div>
              <div style="font-size:.8rem;color:var(--ink-l);margin-top:.2rem;font-family:var(--fu)">
                {{ $p->examPaper->category->name }} · {{ $p->examPaper->total_questions }} Qs · {{ $p->examPaper->duration_minutes }} min
              </div>
              <div style="margin-top:.4rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                @if($p->attempts->count())<span class="badge badge-teal">{{ $p->attempts->count() }} attempt(s)</span>@endif
                <span class="badge badge-gray">{{ $p->retakes_used }}/{{ $p->retakes_allowed }} retakes used</span>
                @php $best = $p->attempts->whereNotNull('percentage')->max('percentage'); @endphp
                @if($best)<span class="badge badge-green">Best: {{ round($best) }}%</span>@endif
                @if($activeAttempt)<span class="badge badge-gold">Resume available</span>@endif
              </div>
            </div>
            <div style="display:flex;gap:.5rem;flex-shrink:0;align-items:center">
              @if($latestAttempt && $latestAttempt->status === 'submitted')
              <a href="{{ route('student.exam.result',$latestAttempt) }}" class="btn btn-ghost btn-sm">Last Result</a>
              @endif
              @if($activeAttempt)
              <a href="{{ route('student.exam.take',$p) }}" class="btn btn-primary btn-sm">Resume Exam →</a>
              @elseif($p->canAttempt())
              <a href="{{ route('student.exam.start',$p) }}" class="btn btn-primary btn-sm">Start Exam →</a>
              @else
              <span class="badge badge-gray" style="padding:.4rem .75rem">No retakes left</span>
              @endif
            </div>
          </div>
          @endforeach
        </div>
        <div style="margin-top:1.5rem">{{ $purchases->links() }}</div>
      @else
        <div class="card card-static card-body text-center" style="padding:4rem 2rem">
          <div style="font-size:3rem;margin-bottom:1rem">📚</div>
          <h3>No exams purchased yet</h3>
          <p class="mt-2 mb-3">Browse and buy mock tests to start practising for your competitive exam.</p>
          <a href="{{ route('exams.browse') }}" class="btn btn-primary">Browse Exams</a>
        </div>
      @endif
    </main>
  </div>
</div>
@endsection
