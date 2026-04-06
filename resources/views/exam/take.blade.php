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
@php
  $initialRemaining = max(
      $purchase->examPaper->duration_minutes * 60 - optional($attempt->started_at)->diffInSeconds(now()),
      0
  );
  $bookmarks = $attempt->bookmarked_questions ?? [];
  $examSections = collect($purchase->examPaper->exam_sections ?? []);
@endphp
<script>window.__examMode=true;</script>

<div style="padding:1rem;max-width:1100px;margin:0 auto">
  <div class="exam-topbar">
    <div>
      <div style="font-weight:600;font-size:.95rem;font-family:var(--fu)">{{ $purchase->examPaper->title }}</div>
      <div style="font-size:.8rem;opacity:.7;font-family:var(--fu)">
        {{ count($questions) }} Questions · {{ $purchase->examPaper->max_marks }} Marks · Attempt #{{ max($purchase->retakes_used, 1) }}
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem">
      <div style="text-align:right">
        <div class="exam-timer" id="timer">{{ sprintf('%02d:%02d', floor($initialRemaining / 60), $initialRemaining % 60) }}</div>
        <div id="autosave-status" style="font-size:.72rem;opacity:.75;font-family:var(--fu)">Autosave ready</div>
      </div>
      <button onclick="if(confirm('Submit exam now?'))document.getElementById('exam-form').submit()" class="btn btn-white btn-sm">Submit Exam</button>
    </div>
  </div>

  <form id="exam-form" action="{{ route('student.exam.submit',$purchase) }}" method="POST">
    @csrf
    <input type="hidden" id="tab_switches" name="tab_switches" value="{{ (int) ($attempt->tab_switch_count ?? 0) }}">
    <div id="bookmark-fields">
      @foreach($bookmarks as $serial)
      <input type="hidden" name="bookmarked_questions[]" value="{{ (int) $serial }}" data-bookmark-field="{{ (int) $serial }}">
      @endforeach
    </div>
    <div id="question-timing-fields">
      @foreach(($attempt->question_timings ?? []) as $serial => $seconds)
      <input type="hidden" name="question_timings[{{ $serial }}]" value="{{ (int) $seconds }}" data-qt-field="{{ $serial }}">
      @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 220px;gap:1.25rem;align-items:start">
      <div>
        @if($examSections->count())
        <div class="card card-static" style="margin-bottom:1rem">
          <div class="card-body">
            <h3 style="margin-bottom:.5rem">Exam Sections</h3>
            <div style="display:flex;flex-direction:column;gap:.6rem">
              @foreach($examSections as $section)
              <div style="padding:.7rem .85rem;border-radius:var(--r1);background:var(--border-l)">
                <div style="font-weight:700">{{ $section['name'] ?? 'Section' }}</div>
                @if(!empty($section['description']))
                <div class="text-muted" style="font-size:.8rem;margin-top:.15rem">{{ $section['description'] }}</div>
                @endif
              </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif

        @foreach($questions as $idx => $q)
        @php
          $serial = $q['serial'];
          $savedValue = $savedAnswers[$serial] ?? null;
          $savedValues = is_array($savedValue) ? $savedValue : [$savedValue];
          $savedValues = array_map(fn ($value) => strtoupper(trim((string) $value)), array_filter($savedValues, fn ($value) => filled($value)));
        @endphp
        <div class="question-card" id="q{{ $serial }}" style="{{ $idx > 0 ? 'display:none' : '' }}">
          <div class="q-number">Question {{ $serial }} of {{ count($questions) }} · {{ $q['marks'] ?? 1 }} Mark(s)</div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin:.35rem 0 .9rem;flex-wrap:wrap">
            <div class="text-muted" style="font-size:.78rem;font-family:var(--fu)">
              Section: {{ $q['section'] ?? $q['topic'] ?? $q['subject'] ?? 'General' }}
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleBookmark({{ $serial }})" id="bookmark-btn-{{ $serial }}">
              {{ in_array($serial, $bookmarks, true) ? '★ Bookmarked' : '☆ Bookmark' }}
            </button>
          </div>
          <div class="q-text">{!! nl2br(e($q['text'])) !!}</div>

          @if(!empty($q['image_description']))
          <div class="alert alert-info mb-3" style="font-size:.85rem">📷 {{ $q['image_description'] }}</div>
          @endif

          @if(in_array($q['type'] ?? 'mcq', ['mcq','omr']))
            <div class="opt-list">
              @foreach($q['options'] ?? [] as $opt)
              @php $checked = in_array(strtoupper($opt['label']), $savedValues, true); @endphp
              <label class="opt-item {{ $checked ? 'selected' : '' }}" onclick="selectOption(this,'{{ $serial }}')">
                <input type="radio" name="answers[{{ $serial }}]" value="{{ $opt['label'] }}" {{ $checked ? 'checked' : '' }}>
                <span class="opt-lbl">{{ $opt['label'] }}</span>
                <span class="opt-text">{!! $opt['text'] !!}</span>
              </label>
              @endforeach
            </div>

          @elseif(($q['type'] ?? null) === 'msq')
            <p class="text-muted mb-2" style="font-size:.82rem;font-family:var(--fu)">Select all correct answers:</p>
            <div class="opt-list">
              @foreach($q['options'] ?? [] as $opt)
              @php $checked = in_array(strtoupper($opt['label']), $savedValues, true); @endphp
              <label class="opt-item {{ $checked ? 'selected' : '' }}" onclick="toggleOption(this)">
                <input type="checkbox" name="answers[{{ $serial }}][]" value="{{ $opt['label'] }}" {{ $checked ? 'checked' : '' }}>
                <span class="opt-lbl" style="border-radius:var(--r1)">{{ $opt['label'] }}</span>
                <span class="opt-text">{!! $opt['text'] !!}</span>
              </label>
              @endforeach
            </div>

          @elseif(($q['type'] ?? null) === 'fill_blank')
            <div style="max-width:400px">
              <input type="text" name="answers[{{ $serial }}]" class="form-control" placeholder="Type your answer here..." value="{{ is_array($savedValue) ? '' : $savedValue }}">
            </div>

          @elseif(in_array($q['type'] ?? '', ['short_answer','long_answer']))
            <textarea name="answers[{{ $serial }}]" class="form-control" rows="{{ $q['type']==='long_answer' ? 8 : 4 }}" placeholder="Write your answer here...">{{ is_array($savedValue) ? '' : $savedValue }}</textarea>

          @elseif(($q['type'] ?? null) === 'math')
            <div style="max-width:400px">
              <input type="text" name="answers[{{ $serial }}]" class="form-control" placeholder="Enter numerical/algebraic answer..." value="{{ is_array($savedValue) ? '' : $savedValue }}">
            </div>
          @endif

          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-l)">
            <div style="display:flex;gap:.5rem">
              @if($idx > 0)
              <button type="button" onclick="goToQ({{ $questions[$idx-1]['serial'] }})" class="btn btn-ghost btn-sm">← Previous</button>
              @endif
              <button type="button" onclick="markReview({{ $serial }})" class="btn btn-sm" style="background:var(--gold-l);color:#7A5C10;border-color:var(--gold)">Mark for Review</button>
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

      <div>
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
            @if(!empty($purchase->examPaper->section_time_rules))
            <div style="margin-bottom:.75rem;padding:.75rem;border-radius:var(--r1);background:var(--cream);font-size:.78rem;font-family:var(--fu)">
              <div><strong>Current section:</strong> <span id="current-section-name">General</span></div>
              <div><strong>Section timer:</strong> <span id="current-section-timer">--:--</span></div>
            </div>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;font-size:.76rem;font-family:var(--fu)">
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--teal);border-radius:2px;display:inline-block"></span>Answered</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--saffron);border-radius:2px;display:inline-block"></span>Current</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--gold-l);border:1px solid var(--gold);border-radius:2px;display:inline-block"></span>For Review</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:var(--white);border:1px solid var(--border);border-radius:2px;display:inline-block"></span>Not Visited</div>
              <div style="display:flex;align-items:center;gap:.3rem"><span style="width:14px;height:14px;background:#FFF7E6;border:1px solid #F5D48A;border-radius:2px;display:inline-block"></span>Bookmarked</div>
            </div>
            <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border-l);font-size:.78rem;color:var(--ink-l);font-family:var(--fu)">
              Progress: <strong id="answered-count">0</strong> / {{ count($questions) }} answered
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
const totalSecs = {{ $initialRemaining }};
const autosaveUrl = @json(route('student.exam.autosave', $purchase));
const csrfToken = @json(csrf_token());
const questionSections = @json(collect($questions)->mapWithKeys(fn($q) => [$q['serial'] => ($q['section'] ?? $q['topic'] ?? $q['subject'] ?? 'General')]));
const sectionRules = @json(collect($purchase->examPaper->section_time_rules ?? [])->mapWithKeys(fn($rule) => [$rule['section'] => (int) ($rule['minutes'] ?? 0) * 60]));
const firstQ = {{ $questions[0]['serial'] ?? 1 }};
let remaining = totalSecs;
let currentQ = firstQ;
let lastQuestionStartedAt = Date.now();
let autosaveTimer = null;
let isAutosaving = false;
let pendingAutosave = false;
const marked = new Set();
const bookmarks = new Set(@json(array_map('intval', $bookmarks)));
const timerEl = document.getElementById('timer');
const statusEl = document.getElementById('autosave-status');
const formEl = document.getElementById('exam-form');

function answerElements() {
  return Array.from(formEl.querySelectorAll('input[name^="answers["], textarea[name^="answers["], select[name^="answers["]'));
}

function timingField(serial) {
  let field = document.querySelector(`[data-qt-field="${serial}"]`);
  if (!field) {
    field = document.createElement('input');
    field.type = 'hidden';
    field.name = `question_timings[${serial}]`;
    field.setAttribute('data-qt-field', serial);
    document.getElementById('question-timing-fields').appendChild(field);
  }
  return field;
}

function trackTimeOnCurrentQuestion() {
  const elapsed = Math.max(0, Math.round((Date.now() - lastQuestionStartedAt) / 1000));
  if (!currentQ || elapsed === 0) return;

  const field = timingField(currentQ);
  field.value = Number(field.value || 0) + elapsed;
  lastQuestionStartedAt = Date.now();
}

function buildAnswersPayload() {
  const payload = {};
  answerElements().forEach((element) => {
    const match = element.name.match(/^answers\[(.+?)\](\[\])?$/);
    if (!match) return;

    const key = match[1];
    if (element.type === 'radio') {
      if (element.checked) payload[key] = element.value;
      return;
    }

    if (element.type === 'checkbox') {
      if (!payload[key]) payload[key] = [];
      if (element.checked) payload[key].push(element.value);
      return;
    }

    if (element.value !== '') {
      payload[key] = element.value;
    }
  });

  return payload;
}

function buildQuestionTimingsPayload() {
  const payload = {};
  document.querySelectorAll('[data-qt-field]').forEach((field) => {
    payload[field.getAttribute('data-qt-field')] = Number(field.value || 0);
  });

  return payload;
}

function buildBookmarksPayload() {
  return Array.from(bookmarks.values());
}

function bookmarkField(serial) {
  return document.querySelector(`[data-bookmark-field="${serial}"]`);
}

function syncBookmarkFields() {
  document.querySelectorAll('[data-bookmark-field]').forEach((field) => field.remove());
  bookmarks.forEach((serial) => {
    const field = document.createElement('input');
    field.type = 'hidden';
    field.name = 'bookmarked_questions[]';
    field.value = serial;
    field.setAttribute('data-bookmark-field', serial);
    document.getElementById('bookmark-fields').appendChild(field);
  });
}

function updateAnsweredState() {
  let answeredCount = 0;

  @foreach($questions as $q)
  (function(serial){
    const inputs = Array.from(document.querySelectorAll(`[name="answers[${serial}]"], [name="answers[${serial}][]"]`));
    const answered = inputs.some((input) => {
      if (input.type === 'radio' || input.type === 'checkbox') return input.checked;
      return input.value.trim() !== '';
    });
    document.getElementById('nav'+serial)?.classList.toggle('answered', answered);
    document.getElementById('nav'+serial)?.classList.toggle('marked', bookmarks.has(serial));
    if (answered) answeredCount++;
  })({{ $q['serial'] }});
  @endforeach

  document.getElementById('answered-count').textContent = answeredCount;
}

function updateSectionTimer() {
  const sectionName = questionSections[currentQ] || 'General';
  const sectionNameEl = document.getElementById('current-section-name');
  const sectionTimerEl = document.getElementById('current-section-timer');
  if (!sectionNameEl || !sectionTimerEl) return;

  sectionNameEl.textContent = sectionName;
  const limit = Number(sectionRules[sectionName] || 0);
  if (!limit) {
    sectionTimerEl.textContent = 'No limit';
    sectionTimerEl.style.color = '';
    return;
  }

  const payload = buildQuestionTimingsPayload();
  const used = Object.entries(payload)
    .filter(([serial]) => (questionSections[serial] || 'General') === sectionName)
    .reduce((sum, [, value]) => sum + Number(value || 0), 0);

  const remainingSection = Math.max(0, limit - used);
  sectionTimerEl.textContent = `${String(Math.floor(remainingSection / 60)).padStart(2, '0')}:${String(remainingSection % 60).padStart(2, '0')}`;
  sectionTimerEl.style.color = remainingSection <= 60 ? '#B42318' : remainingSection <= 180 ? '#B7791F' : '';
}

function setAutosaveStatus(message, tone = '') {
  statusEl.textContent = message;
  statusEl.style.color = tone === 'error' ? '#B42318' : tone === 'saving' ? '#0F766E' : '';
}

function queueAutosave() {
  pendingAutosave = true;
  setAutosaveStatus('Saving...', 'saving');

  clearTimeout(autosaveTimer);
  autosaveTimer = setTimeout(() => {
    performAutosave();
  }, 800);
}

function performAutosave(force = false) {
  if (isAutosaving) return;
  if (!pendingAutosave && !force) return;

  isAutosaving = true;
  pendingAutosave = false;

  fetch(autosaveUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      answers: buildAnswersPayload(),
      tab_switches: document.getElementById('tab_switches').value,
      question_timings: buildQuestionTimingsPayload(),
      bookmarked_questions: buildBookmarksPayload()
    })
  })
  .then(async (response) => {
    if (!response.ok) throw new Error('Autosave failed');
    return response.json();
  })
  .then((data) => {
    setAutosaveStatus(`Saved at ${data.saved_at}`);
    document.getElementById('answered-count').textContent = data.answered;
  })
  .catch(() => {
    pendingAutosave = true;
    setAutosaveStatus('Autosave retry pending', 'error');
  })
  .finally(() => {
    isAutosaving = false;
  });
}

if (remaining <= 0) {
  formEl.submit();
} else {
  setInterval(() => {
    remaining--;
    if (remaining <= 0) {
      formEl.submit();
      return;
    }

    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    timerEl.textContent = `${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
    timerEl.className = 'exam-timer' + (remaining <= 300 ? ' danger' : remaining <= 600 ? ' warn' : '');
    updateSectionTimer();
  }, 1000);
}

function goToQ(serial) {
  trackTimeOnCurrentQuestion();
  document.getElementById('q' + currentQ).style.display = 'none';
  document.getElementById('nav' + currentQ)?.classList.remove('current');
  currentQ = serial;
  document.getElementById('q' + serial).style.display = 'block';
  document.getElementById('nav' + serial)?.classList.add('current');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  updateSectionTimer();
}

function selectOption(label, serial) {
  label.closest('.opt-list').querySelectorAll('.opt-item').forEach((item) => item.classList.remove('selected'));
  label.classList.add('selected');
  document.getElementById('nav' + serial)?.classList.add('answered');
  queueAutosave();
}

function toggleOption(label) {
  label.classList.toggle('selected');
  updateAnsweredState();
  queueAutosave();
}

function markReview(serial) {
  if (marked.has(serial)) {
    marked.delete(serial);
  } else {
    marked.add(serial);
  }
  document.getElementById('nav' + serial)?.classList.toggle('marked', marked.has(serial));
}

function toggleBookmark(serial) {
  if (bookmarks.has(serial)) {
    bookmarks.delete(serial);
  } else {
    bookmarks.add(serial);
  }
  syncBookmarkFields();
  document.getElementById(`bookmark-btn-${serial}`).textContent = bookmarks.has(serial) ? '★ Bookmarked' : '☆ Bookmark';
  updateAnsweredState();
  queueAutosave();
}

document.getElementById('nav' + firstQ)?.classList.add('current');
updateAnsweredState();
updateSectionTimer();
syncBookmarkFields();

answerElements().forEach((element) => {
  element.addEventListener('change', () => {
    updateAnsweredState();
    queueAutosave();
  });
  element.addEventListener('input', () => {
    updateAnsweredState();
    queueAutosave();
  });
});

document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    trackTimeOnCurrentQuestion();
    const tabSwitchField = document.getElementById('tab_switches');
    tabSwitchField.value = Number(tabSwitchField.value || 0) + 1;
    queueAutosave();
  } else {
    lastQuestionStartedAt = Date.now();
  }
});

formEl.addEventListener('submit', () => {
  trackTimeOnCurrentQuestion();
});

window.addEventListener('beforeunload', () => {
  trackTimeOnCurrentQuestion();
  performAutosave(true);
});
</script>
@endpush
@endsection
