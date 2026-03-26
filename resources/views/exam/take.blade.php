@extends('layouts.app')
@section('title', 'Taking: '.$purchase->examPaper->title)
@push('head')
<style>
body{background:#F0F4F8}
.navbar{display:none}
footer{display:none}
</style>
@endpush
@section('content')
@php window.__examMode = true; @endphp
<script>window.__examMode=true;</script>

@if(!empty($taoLaunchUrl))
  <div style="height:100vh;width:100vw;overflow:hidden;margin:0;padding:0">
    <iframe
      src="{{ $taoLaunchUrl }}"
      title="TAO Exam"
      style="border:0;width:100%;height:100%;display:block"
      allow="fullscreen"
    ></iframe>
  </div>
@else
<div style="padding:1rem;max-width:1100px;margin:0 auto">
  {{-- Sticky exam top bar --}}
  <div class="exam-topbar">
    <div>
      <div style="font-weight:600;font-size:.95rem;font-family:var(--fu)">{{ $purchase->examPaper->title }}</div>
      <div style="font-size:.8rem;opacity:.7;font-family:var(--fu)">{{ count($questions) }} Questions · {{ $purchase->examPaper->max_marks }} Marks</div>
    </div>
    <div class="exam-timer" id="timer">{{ sprintf('%02d:%02d',floor($purchase->examPaper->duration_minutes),0) }}</div>
    <button onclick="if(confirm('Submit exam now?'))document.getElementById('exam-form').submit()" class="btn btn-white btn-sm">Submit Exam</button>
  </div>

  <form id="exam-form" action="{{ route('student.exam.submit',$purchase) }}" method="POST">
    @csrf
    <input type="hidden" id="tab_switches" name="tab_switches" value="0">

    <div style="display:grid;grid-template-columns:1fr 220px;gap:1.25rem;align-items:start">
      {{-- QUESTION AREA --}}
      <div>
        @foreach($questions as $idx => $q)
        <div class="question-card" id="q{{ $q['serial'] }}" style="{{ $idx > 0 ? 'display:none' : '' }}">
          <div class="q-number">Question {{ $q['serial'] }} of {{ count($questions) }} · {{ $q['marks'] ?? 1 }} Mark(s)</div>
          <div class="q-text">{!! nl2br(e($q['text'])) !!}</div>

          @if(!empty($q['image_description']))
          <div class="alert alert-info mb-3" style="font-size:.85rem">📷 {{ $q['image_description'] }}</div>
          @endif

          @if(in_array($q['type']??'mcq',['mcq','omr']))
            <div class="opt-list">
              @foreach($q['options']??[] as $opt)
              <label class="opt-item" onclick="selectOption(this,'{{ $q['serial'] }}')">
                <input type="radio" name="answers[{{ $q['serial'] }}]" value="{{ $opt['label'] }}">
                <span class="opt-lbl">{{ $opt['label'] }}</span>
                <span class="opt-text">{!! $opt['text'] !!}</span>
              </label>
              @endforeach
            </div>

          @elseif($q['type']==='msq')
            <p class="text-muted mb-2" style="font-size:.82rem;font-family:var(--fu)">Select all correct answers:</p>
            <div class="opt-list">
              @foreach($q['options']??[] as $opt)
              <label class="opt-item" onclick="toggleOption(this)">
                <input type="checkbox" name="answers[{{ $q['serial'] }}][]" value="{{ $opt['label'] }}">
                <span class="opt-lbl" style="border-radius:var(--r1)">{{ $opt['label'] }}</span>
                <span class="opt-text">{!! $opt['text'] !!}</span>
              </label>
              @endforeach
            </div>

          @elseif($q['type']==='fill_blank')
            <div style="max-width:400px">
              <input type="text" name="answers[{{ $q['serial'] }}]" class="form-control" placeholder="Type your answer here…">
            </div>

          @elseif(in_array($q['type']??'',['short_answer','long_answer']))
            <textarea name="answers[{{ $q['serial'] }}]" class="form-control" rows="{{ $q['type']==='long_answer'?8:4 }}" placeholder="Write your answer here…"></textarea>

          @elseif($q['type']==='math')
            <div style="max-width:400px">
              <input type="text" name="answers[{{ $q['serial'] }}]" class="form-control" placeholder="Enter numerical/algebraic answer…">
            </div>
          @endif

          {{-- Navigation --}}
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-l)">
            <div style="display:flex;gap:.5rem">
              @if($idx > 0)
              <button type="button" onclick="goToQ({{ $questions[$idx-1]['serial'] }})" class="btn btn-ghost btn-sm">← Previous</button>
              @endif
              <button type="button" onclick="markReview({{ $q['serial'] }})" class="btn btn-sm" style="background:var(--gold-l);color:#7A5C10;border-color:var(--gold)">Mark for Review</button>
            </div>
            @if($idx < count($questions)-1)
            <button type="button" onclick="goToQ({{ $questions[$idx+1]['serial'] }})" class="btn btn-teal btn-sm">Next →</button>
            @else
            <button type="button" onclick="if(confirm('Submit exam?'))document.getElementById('exam-form').submit()" class="btn btn-primary btn-sm">Submit Exam ✓</button>
            @endif
          </div>
        </div>
        @endforeach
      </div>

      {{-- SIDEBAR --}}
      <div>
        {{-- Question navigator --}}
        <div class="card card-static" style="position:sticky;top:80px">
          <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-l);font-size:.82rem;font-weight:600;font-family:var(--fu)">Question Navigator</div>
          <div style="padding:.75rem">
            <div class="q-nav">
              @foreach($questions as $q)
              <button type="button" class="q-btn" id="nav{{ $q['serial'] }}" onclick="goToQ({{ $q['serial'] }})">{{ $q['serial'] }}</button>
              @endforeach
            </div>
          </div>
          <div style="padding:.75rem;border-top:1px solid var(--border-l)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;font-size:.76rem;font-family:var(--fu)">
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--teal);border-radius:2px;display:inline-block"></span>Answered</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--saffron);border-radius:2px;display:inline-block"></span>Current</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--gold-l);border:1px solid var(--gold);border-radius:2px;display:inline-block"></span>For Review</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--white);border:1px solid var(--border);border-radius:2px;display:inline-block"></span>Not Visited</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
const totalSecs = {{ $purchase->examPaper->duration_minutes * 60 }};
let remaining = totalSecs;
const firstQ = {{ $questions[0]['serial'] ?? 1 }};
let currentQ = firstQ;
const marked = new Set();

// Timer
const timerEl = document.getElementById('timer');
const t = setInterval(()=>{
  remaining--;
  if(remaining<=0){clearInterval(t);document.getElementById('exam-form').submit();return}
  const m=Math.floor(remaining/60), s=remaining%60;
  timerEl.textContent=`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  timerEl.className='exam-timer' + (remaining<=300?' danger':remaining<=600?' warn':'');
},1000);

function goToQ(serial){
  document.getElementById('q'+currentQ).style.display='none';
  document.getElementById('nav'+currentQ).classList.remove('current');
  currentQ=serial;
  document.getElementById('q'+serial).style.display='block';
  document.getElementById('nav'+serial).classList.add('current');
  window.scrollTo({top:0,behavior:'smooth'});
  renderMath();
}

function selectOption(label,serial){
  label.closest('.opt-list').querySelectorAll('.opt-item').forEach(i=>i.classList.remove('selected'));
  label.classList.add('selected');
  document.getElementById('nav'+serial).classList.add('answered');
}

function toggleOption(label){
  label.classList.toggle('selected');
  const serial=label.closest('.question-card').id.replace('q','');
  const anyChecked=label.closest('.opt-list').querySelectorAll('input:checked').length>0;
  document.getElementById('nav'+serial).classList.toggle('answered',anyChecked);
}

function markReview(serial){
  marked.has(serial)?marked.delete(serial):marked.add(serial);
  document.getElementById('nav'+serial).classList.toggle('marked',marked.has(serial));
}

// Init — mark first as current
document.getElementById('nav'+firstQ)?.classList.add('current');
renderMath();
</script>
@endpush
@endif
@endsection
