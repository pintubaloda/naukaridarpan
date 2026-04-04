@extends('layouts.app')
@section('title','Result — '.$attempt->examPaper->title)
@section('content')
<div class="container" style="padding:2rem 1.25rem 4rem">
  {{-- Result header --}}
  <div class="card card-static mb-4" style="background:var(--teal);border-color:var(--teal)">
    <div style="padding:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:2rem">
      <div>
        <div style="font-size:.82rem;color:rgba(255,255,255,.65);font-family:var(--fu);margin-bottom:.5rem">{{ $attempt->examPaper->category->name ?? '' }} · {{ $attempt->examPaper->title }}</div>
        <h2 style="color:#fff;margin-bottom:.25rem">Your Result</h2>
        <div style="font-size:.88rem;color:rgba(255,255,255,.65);font-family:var(--fu)">Submitted {{ $attempt->submitted_at?->format('d M Y, g:i A') }}</div>
      </div>
      <div class="score-ring">
        <span class="score-n">{{ round($attempt->percentage) }}%</span>
        <span class="score-t">{{ $attempt->score }}/{{ $attempt->examPaper->max_marks }}</span>
      </div>
    </div>
  </div>

  {{-- Stats grid --}}
  <div class="g-grid grid-4 mb-4">
    @foreach([
      ['icon-green','✓','Correct',$attempt->correct_answers,'text-ok'],
      ['icon-teal','✗','Wrong',$attempt->wrong_answers,'text-err'],
      ['icon-gold','–','Unattempted',$attempt->unattempted,'text-muted'],
      ['icon-saffron','⏱','Time Taken',gmdate('i:s',$attempt->time_taken_seconds??0).' min','text-saffron'],
    ] as [$ic,$sym,$lbl,$val,$cls])
    <div class="stat-card">
      <div class="stat-icon {{ $ic }}" style="font-size:1.2rem">{{ $sym }}</div>
      <div class="stat-label">{{ $lbl }}</div>
      <div class="stat-val {{ $cls }}" style="font-size:1.5rem">{{ $val }}</div>
    </div>
    @endforeach
  </div>

  @if($attempt->tab_switch_count > 0)
  <div class="alert alert-warning mb-4">⚠️ {{ $attempt->tab_switch_count }} tab switch(es) detected during this exam.</div>
  @endif

  @if(!empty($attempt->tao_result))
  <div class="alert alert-info mb-4">TAO-backed exam result has been synced for this attempt.</div>
  @endif

  {{-- Answer key --}}
  @if(!empty($questions))
  <h3 class="mb-3">Answer Review</h3>
  @php $answers = is_array($attempt->answers) ? $attempt->answers : json_decode($attempt->answers,true); @endphp
  <div style="display:flex;flex-direction:column;gap:1rem">
    @foreach($questions as $q)
    @php
      $serial    = $q['serial'];
      $given     = $answers[$serial] ?? null;
      $givenArr  = $given ? (is_array($given)?$given:[$given]) : [];
      $correctArr= is_array($q['correct_answer']??null)?$q['correct_answer']:[$q['correct_answer']??''];
      $isCorrect = !empty($givenArr) && array_map('strtoupper',$givenArr)===array_map('strtoupper',$correctArr);
      $isSkipped = empty($givenArr);
    @endphp
    <div class="card card-static card-body" style="border-left:4px solid {{ $isSkipped?'var(--border)':($isCorrect?'var(--ok)':'var(--err)') }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:.75rem">
        <div class="q-number">Q{{ $serial }} · {{ $q['marks']??1 }} mark(s)</div>
        <span class="badge {{ $isSkipped?'badge-gray':($isCorrect?'badge-green':'badge-red') }}">
          {{ $isSkipped?'Skipped':($isCorrect?'Correct':'Wrong') }}
        </span>
      </div>
      <div style="font-size:.95rem;line-height:1.7;margin-bottom:1rem">{!! nl2br(e($q['text'])) !!}</div>

      @if(!empty($q['options']))
      <div style="display:flex;flex-direction:column;gap:.4rem">
        @foreach($q['options'] as $opt)
        @php
          $isThisCorrect = in_array(strtoupper($opt['label']), array_map('strtoupper',$correctArr));
          $isThisGiven   = in_array(strtoupper($opt['label']), array_map('strtoupper',$givenArr));
        @endphp
        <div style="display:flex;align-items:flex-start;gap:.6rem;padding:.5rem .75rem;border-radius:var(--r1);background:{{ $isThisCorrect?'#F0FFF4':($isThisGiven&&!$isThisCorrect?'#FFF5F5':'var(--border-l)') }};border:1px solid {{ $isThisCorrect?'#C6F6D5':($isThisGiven&&!$isThisCorrect?'#FED7D7':'transparent') }}">
          <span style="font-weight:700;font-size:.82rem;color:var(--ink-l);width:20px;flex-shrink:0;font-family:var(--fu)">{{ $opt['label'] }}</span>
          <span style="font-size:.9rem">{!! $opt['text'] !!}</span>
          @if($isThisCorrect)<span style="color:var(--ok);margin-left:auto;font-size:.8rem;font-family:var(--fu)">✓ Correct</span>@endif
          @if($isThisGiven && !$isThisCorrect)<span style="color:var(--err);margin-left:auto;font-size:.8rem;font-family:var(--fu)">✗ Your answer</span>@endif
        </div>
        @endforeach
      </div>
      @endif

      @if(!empty($q['explanation']))
      <div style="margin-top:.75rem;padding:.75rem;background:var(--gold-l);border-radius:var(--r1);font-size:.88rem;line-height:1.6;border:1px solid rgba(212,160,23,.3)">
        <strong style="font-family:var(--fu)">Explanation:</strong> {{ $q['explanation'] }}
      </div>
      @endif
    </div>
    @endforeach
  </div>
  @endif

  {{-- Action buttons --}}
  <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
    <a href="{{ route('student.exams') }}" class="btn btn-teal">← Back to My Exams</a>
    @if($attempt->purchase->canAttempt())
    <a href="{{ route('student.exam.start',$attempt->purchase) }}" class="btn btn-outline">Retake Exam ({{ $attempt->purchase->retakes_allowed - $attempt->purchase->retakes_used }} left)</a>
    @endif
    <a href="{{ route('exams.browse') }}" class="btn btn-ghost">Browse More Exams</a>
  </div>
</div>
@endsection
