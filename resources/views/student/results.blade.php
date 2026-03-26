@extends('layouts.app')
@section('title','My Results — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.student-sidebar')
    <main>
      <h2 class="mb-1">My Results</h2>
      <p class="text-muted mb-4">All exam attempts and scores</p>
      @if($attempts->count())
        <div class="tbl-wrap">
          <table class="tbl">
            <thead><tr><th>Exam</th><th>Date</th><th>Score</th><th>Percentage</th><th>Correct</th><th>Wrong</th><th>Time</th><th></th></tr></thead>
            <tbody>
              @foreach($attempts as $a)
              <tr>
                <td style="font-weight:500;font-family:var(--fu);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $a->examPaper->title }}</td>
                <td class="text-muted" style="white-space:nowrap">{{ $a->submitted_at?->format('d M Y') }}</td>
                <td><span class="fw-600">{{ $a->score }}/{{ $a->examPaper->max_marks }}</span></td>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="prog" style="width:60px"><div class="prog-bar" style="width:{{ $a->percentage }}%;background:{{ $a->percentage>=60?'var(--ok)':($a->percentage>=40?'var(--warn)':'var(--err)') }}"></div></div>
                    <span style="font-size:.82rem;font-weight:600;color:{{ $a->percentage>=60?'var(--ok)':($a->percentage>=40?'var(--warn)':'var(--err)') }}">{{ round($a->percentage) }}%</span>
                  </div>
                </td>
                <td class="text-ok fw-600">{{ $a->correct_answers }}</td>
                <td style="color:var(--err);font-weight:600">{{ $a->wrong_answers }}</td>
                <td class="text-muted" style="white-space:nowrap">{{ $a->time_taken_seconds ? gmdate('i:s',$a->time_taken_seconds).' min' : '—' }}</td>
                <td><a href="{{ route('student.exam.result',$a) }}" class="btn btn-ghost btn-sm">Review</a></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:1.5rem">{{ $attempts->links() }}</div>
      @else
        <div class="card card-static card-body text-center" style="padding:4rem 2rem">
          <div style="font-size:3rem;margin-bottom:1rem">📊</div>
          <h3>No attempts yet</h3>
          <p class="mt-2 mb-3">Take your first exam to see results here.</p>
          <a href="{{ route('student.exams') }}" class="btn btn-primary">Go to My Exams</a>
        </div>
      @endif
    </main>
  </div>
</div>
@endsection
