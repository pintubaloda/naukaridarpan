@extends('layouts.app')
@section('title','Ready to Start — '.$purchase->examPaper->title)
@section('content')
<div class="container" style="padding:3rem 1.25rem;max-width:700px;margin:0 auto">
  <div class="card card-static" style="overflow:hidden">
    <div style="background:var(--teal);padding:2rem;text-align:center">
      <div style="font-size:3rem;margin-bottom:.75rem">📝</div>
      <h2 style="color:#fff;margin-bottom:.4rem">{{ $purchase->examPaper->title }}</h2>
      <p style="color:rgba(255,255,255,.7);font-size:.9rem">{{ $purchase->examPaper->category->name ?? '' }}</p>
    </div>
    <div class="card-body" style="padding:2rem">
      @if($activeAttempt)
      <div class="alert alert-warning mb-3">
        <strong>Resume available:</strong> You have an in-progress attempt for this exam. We will continue from your saved answers.
      </div>
      @endif

      @if($latestAttempt && $latestAttempt->status === 'submitted')
      <div class="alert alert-success mb-3">
        <strong>Latest result:</strong> {{ number_format($latestAttempt->score ?? 0, 2) }} / {{ $purchase->examPaper->max_marks }}
        ({{ number_format($latestAttempt->percentage ?? 0, 2) }}%)
      </div>
      @endif

      <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center;margin-bottom:2rem">
        @foreach([['📝',$purchase->examPaper->total_questions,'Questions'],['⏱',$purchase->examPaper->duration_minutes.' min','Duration'],['🏆',$purchase->examPaper->max_marks,'Total Marks']] as [$i,$v,$l])
        <div style="background:var(--cream);border-radius:var(--r2);padding:1rem">
          <div style="font-size:1.5rem;margin-bottom:.3rem">{{ $i }}</div>
          <div style="font-weight:700;font-family:var(--fd);font-size:1.2rem;color:var(--teal)">{{ $v }}</div>
          <div style="font-size:.78rem;color:var(--ink-l);font-family:var(--fu)">{{ $l }}</div>
        </div>
        @endforeach
      </div>

      @if($purchase->examPaper->negative_marking > 0)
      <div class="alert alert-warning mb-3"><strong>Negative Marking:</strong> -{{ $purchase->examPaper->negative_marking }} marks per wrong answer. Leave blank if unsure.</div>
      @endif

      <div class="alert alert-info mb-4">
        <div>
          <strong>Before you begin:</strong>
          <ul style="margin:.5rem 0 0 1.25rem;font-size:.88rem">
            <li>Ensure a stable internet connection</li>
            <li>Do not switch tabs or minimise the browser — it will be logged</li>
            <li>The timer starts immediately when you click Start</li>
            <li>You have {{ max($purchase->retakes_allowed - $purchase->retakes_used, 0) }} new attempt(s) remaining after this one</li>
            <li>Your progress is auto-saved while you answer</li>
            <li>Submit before time runs out — the exam auto-submits when the timer ends</li>
          </ul>
        </div>
      </div>

      <div style="display:flex;gap:1rem;justify-content:center">
        <a href="{{ route('student.exams') }}" class="btn btn-ghost">← Back</a>
        <a href="{{ route('student.exam.take',$purchase) }}" class="btn btn-primary btn-lg">{{ $activeAttempt ? 'Resume Exam →' : 'Start Exam Now →' }}</a>
      </div>
      <p class="text-muted text-center mt-2" style="font-size:.8rem">
        {{ $activeAttempt ? 'Continuing your current attempt' : 'Attempt '.($purchase->retakes_used + 1).' of '.$purchase->retakes_allowed }}
      </p>
    </div>
  </div>
</div>
@endsection
