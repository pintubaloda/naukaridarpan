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
  <div class="g-grid grid-3 mb-4">
    <div class="stat-card">
      <div class="stat-label">Rank</div>
      <div class="stat-val text-teal" style="font-size:1.4rem">{{ $attempt->rank_position ? '#'.$attempt->rank_position : '—' }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Percentile</div>
      <div class="stat-val text-saffron" style="font-size:1.4rem">{{ $attempt->percentile !== null ? number_format($attempt->percentile, 2).'%' : '—' }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Integrity Review</div>
      <div class="stat-val {{ ($review['risk_level'] ?? 'low') === 'high' ? 'text-err' : (($review['risk_level'] ?? 'low') === 'medium' ? 'text-saffron' : 'text-ok') }}" style="font-size:1.4rem">{{ ucfirst($review['risk_level'] ?? 'low') }}</div>
    </div>
  </div>

  @if(!empty($analysis))
  <div class="g-grid" style="grid-template-columns:1.2fr .8fr;gap:1rem;margin-bottom:1.5rem">
    <div class="card card-static">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem">
          <div>
            <h3 style="margin-bottom:.2rem">Section Performance</h3>
            <p class="text-muted" style="font-size:.84rem">We group questions by section, topic, or subject when that data exists.</p>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.75rem">
          @foreach(collect($analysis['sections'] ?? [])->sortByDesc('score') as $group)
          <div style="padding:.85rem 1rem;border:1px solid var(--border-l);border-radius:var(--r2);background:#fff">
            <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start">
              <div>
                <div style="font-weight:700">{{ $group['label'] }}</div>
                <div class="text-muted" style="font-size:.8rem">{{ $group['correct'] }} correct · {{ $group['wrong'] }} wrong · {{ $group['unattempted'] }} skipped</div>
              </div>
              <div style="text-align:right">
                <div style="font-weight:700;color:var(--teal)">{{ number_format($group['score'], 2) }}</div>
                <div class="text-muted" style="font-size:.8rem">{{ $group['accuracy'] !== null ? number_format($group['accuracy'], 1).'%' : 'No attempts yet' }}</div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="card card-static">
      <div class="card-body">
        <h3 style="margin-bottom:.2rem">Question Type Analysis</h3>
        <p class="text-muted mb-3" style="font-size:.84rem">Useful for spotting whether accuracy drops on MSQ, math, or descriptive questions.</p>
        <div style="display:flex;flex-direction:column;gap:.75rem">
          @foreach(collect($analysis['question_types'] ?? [])->sortByDesc('score') as $group)
          <div style="padding:.85rem 1rem;border-radius:var(--r2);background:var(--border-l)">
            <div style="display:flex;justify-content:space-between;gap:.75rem">
              <strong>{{ strtoupper(str_replace('_', ' ', $group['label'])) }}</strong>
              <span>{{ number_format($group['score'], 2) }}</span>
            </div>
            <div class="text-muted" style="font-size:.8rem;margin-top:.35rem">
              {{ $group['correct'] }} correct · {{ $group['wrong'] }} wrong · {{ $group['unattempted'] }} skipped
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(!empty($attempt->examPaper->exam_sections))
  <div class="card card-static mb-4">
    <div class="card-body">
      <h3 style="margin-bottom:.2rem">Section Structure</h3>
      <p class="text-muted mb-3" style="font-size:.84rem">The attempt was evaluated against these configured exam sections.</p>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        @foreach($attempt->examPaper->exam_sections as $section)
        <div style="padding:.85rem 1rem;border-radius:var(--r2);background:var(--border-l);min-width:220px">
          <div style="font-weight:700">{{ $section['name'] ?? 'Section' }}</div>
          @if(!empty($section['description']))
          <div class="text-muted" style="font-size:.8rem;margin-top:.25rem">{{ $section['description'] }}</div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  @if(!empty($timingInsights))
  <div class="g-grid grid-3 mb-4">
    <div class="stat-card">
      <div class="stat-label">Tracked Thinking Time</div>
      <div class="stat-val text-teal" style="font-size:1.35rem">{{ gmdate('i:s', $timingInsights['tracked_total_seconds'] ?? 0) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Average Per Question</div>
      <div class="stat-val text-saffron" style="font-size:1.35rem">{{ gmdate('i:s', $timingInsights['avg_seconds'] ?? 0) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Exam Duration</div>
      <div class="stat-val text-ok" style="font-size:1.35rem">{{ gmdate('i:s', $timingInsights['time_taken_seconds'] ?? 0) }}</div>
    </div>
  </div>

  @if(!empty($timingInsights['slowest']))
  <div class="card card-static mb-4">
    <div class="card-body">
      <h3 style="margin-bottom:.2rem">Questions That Took The Most Time</h3>
      <p class="text-muted mb-3" style="font-size:.84rem">This is helpful for identifying where the student slowed down or second-guessed answers.</p>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        @foreach($timingInsights['slowest'] as $serial => $seconds)
        <div style="padding:.75rem 1rem;border-radius:var(--r2);background:var(--border-l);min-width:140px">
          <div style="font-weight:700">Q{{ $serial }}</div>
          <div class="text-muted" style="font-size:.8rem">{{ gmdate('i:s', $seconds) }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif
  @endif

  @if(!empty($weakAreas) && $weakAreas->count())
  <div class="card card-static mb-4">
    <div class="card-body">
      <h3 style="margin-bottom:.2rem">Focus Areas For Your Next Attempt</h3>
      <p class="text-muted mb-3" style="font-size:.84rem">These are the areas where accuracy dropped or questions were skipped more often.</p>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        @foreach($weakAreas as $group)
        <div style="padding:.85rem 1rem;border-radius:var(--r2);background:#FFF7E6;border:1px solid #F5D48A;min-width:180px">
          <div style="font-weight:700">{{ $group['label'] }}</div>
          <div class="text-muted" style="font-size:.8rem;margin-top:.25rem">
            {{ $group['accuracy'] !== null ? number_format($group['accuracy'], 1).'%' : 'No accuracy yet' }} accuracy
          </div>
          <div class="text-muted" style="font-size:.8rem">
            {{ $group['wrong'] }} wrong · {{ $group['unattempted'] }} skipped
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  @if(!empty($recommendedExams) && $recommendedExams->count())
  <div class="mb-4">
    <div style="display:flex;justify-content:space-between;align-items:end;gap:1rem;margin-bottom:1rem;flex-wrap:wrap">
      <div>
        <h3 style="margin-bottom:.2rem">Recommended Next Practice</h3>
        <p class="text-muted" style="font-size:.84rem">A few related exams you can use to reinforce the same category or weak areas.</p>
      </div>
      <a href="{{ route('exams.browse') }}" class="btn btn-ghost btn-sm">Browse all exams</a>
    </div>
    <div class="g-grid grid-3">
      @foreach($recommendedExams as $exam)
        @include('components.exam-card', ['exam' => $exam])
      @endforeach
    </div>
  </div>
  @endif

  @if(!empty($leaderboard) && $leaderboard->count())
  <div class="card card-static mb-4">
    <div class="card-body">
      <h3 style="margin-bottom:.2rem">Leaderboard Snapshot</h3>
      <p class="text-muted mb-3" style="font-size:.84rem">Top recent submitted attempts for this exam, ranked by score percentage and then speed.</p>
      <div class="tbl-wrap" style="border:none;border-radius:0;margin:0">
        <table class="tbl">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student</th>
              <th>Score</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            @foreach($leaderboard as $index => $entry)
            <tr style="{{ $entry->id === $attempt->id ? 'background:rgba(15,118,110,.06)' : '' }}">
              <td>#{{ $index + 1 }}</td>
              <td>
                {{ $entry->student->name ?? 'Student' }}
                @if($entry->id === $attempt->id)
                  <span class="badge badge-teal" style="margin-left:.4rem">You</span>
                @endif
              </td>
              <td>{{ number_format($entry->percentage ?? 0, 2) }}%</td>
              <td>{{ gmdate('i:s', $entry->time_taken_seconds ?? 0) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  @if(!empty($review))
  <div class="card card-static mb-4">
    <div class="card-body">
      <h3 style="margin-bottom:.2rem">Anti-Cheat Review</h3>
      <p class="text-muted mb-3" style="font-size:.84rem">This is an automated integrity review based on tab switches, pacing, and attempt behaviour.</p>
      @if(!empty($review['alerts']))
        <div style="display:flex;flex-direction:column;gap:.5rem">
          @foreach($review['alerts'] as $alert)
          <div class="alert alert-warning" style="margin:0">{{ $alert }}</div>
          @endforeach
        </div>
      @else
        <div class="alert alert-success" style="margin:0">No suspicious behaviour was flagged for this attempt.</div>
      @endif
    </div>
  </div>
  @endif

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
      $normalizedGiven = array_values(array_filter(array_map(fn($item) => strtoupper(trim((string)$item)), $givenArr)));
      $normalizedCorrect = array_values(array_filter(array_map(fn($item) => strtoupper(trim((string)$item)), $correctArr)));
      sort($normalizedGiven);
      sort($normalizedCorrect);
      $isCorrect = !empty($normalizedGiven) && $normalizedGiven === $normalizedCorrect;
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
