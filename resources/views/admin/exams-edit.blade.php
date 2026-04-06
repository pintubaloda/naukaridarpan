@extends('layouts.app')
@section('title','Edit Exam — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main style="max-width:820px;width:100%">
      <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.exams.index') }}" style="font-size:.85rem;color:var(--ink-l)">← Back to Manage Exams</a>
        <h2 class="mt-1">Edit Exam</h2>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-error mb-3">{{ session('error') }}</div>@endif
      @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif

  @php
    $storedPdfUrl = $paper->original_file ? \Illuminate\Support\Facades\Storage::disk('public')->url($paper->original_file) : null;
  @endphp

  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Paper Intake & Parse</div>
    <div class="card-body">
      <div class="g-grid" style="grid-template-columns:1.35fr .95fr;gap:1rem;align-items:start">
        <div style="display:grid;gap:.75rem">
          <div>
            <div class="text-muted" style="font-size:.78rem">Saved PDF</div>
            <div style="margin-top:.2rem;font-weight:600">
              @if($storedPdfUrl)
                <a href="{{ $storedPdfUrl }}" target="_blank" rel="noopener">Open saved paper PDF</a>
              @elseif($paper->source_url)
                <a href="{{ $paper->source_url }}" target="_blank" rel="noopener">Open source PDF URL</a>
              @else
                <span class="text-muted">No PDF attached yet</span>
              @endif
            </div>
          </div>
          <div>
            <div class="text-muted" style="font-size:.78rem">Parse Status</div>
            <div id="parse-status-label" style="margin-top:.2rem;font-weight:600">{{ ucfirst(str_replace('_', ' ', $paper->parse_status ?? 'pending')) }}</div>
            <div style="margin-top:.55rem;background:var(--border-l);border-radius:999px;height:10px;overflow:hidden">
              @php
                $initialProgress = match ($paper->parse_status) {
                    'pending' => str_contains((string) $paper->parse_log, 'Queued') ? 25 : 5,
                    'processing' => 65,
                    'done' => 100,
                    'failed' => 100,
                    default => 5,
                };
              @endphp
              <div id="parse-progress-bar" style="height:100%;width:{{ $initialProgress }}%;background:{{ $paper->parse_status === 'failed' ? 'var(--err)' : 'linear-gradient(90deg, var(--teal), #79c8ff)' }};transition:width .3s ease"></div>
            </div>
            <div id="parse-progress-note" class="text-muted" style="font-size:.8rem;margin-top:.35rem">{{ $initialProgress }}% complete</div>
            @if($paper->parse_log)
            <div id="parse-log-text" class="text-muted" style="font-size:.82rem;margin-top:.35rem;white-space:pre-wrap">{{ $paper->parse_log }}</div>
            @else
            <div id="parse-log-text" class="text-muted" style="font-size:.82rem;margin-top:.35rem;white-space:pre-wrap"></div>
            @endif
          </div>
          <div class="g-grid" style="grid-template-columns:repeat(3, minmax(0,1fr));gap:.6rem">
            <div class="card card-static card-body" style="padding:.75rem .85rem">
              <div class="text-muted" style="font-size:.76rem">PDF Mode</div>
              <div style="font-weight:700;margin-top:.2rem">{{ strtoupper($paper->pdf_kind ?? 'text') }}</div>
            </div>
            <div class="card card-static card-body" style="padding:.75rem .85rem">
              <div class="text-muted" style="font-size:.76rem">Answer Key</div>
              <div style="font-weight:700;margin-top:.2rem">{{ ucfirst(str_replace('_', ' ', $paper->answer_key_mode ?? 'same_pdf')) }}</div>
            </div>
            <div class="card card-static card-body" style="padding:.75rem .85rem">
              <div class="text-muted" style="font-size:.76rem">Question Bank</div>
              <div style="font-weight:700;margin-top:.2rem">{{ app(\App\Services\Exams\QuestionBankSyncService::class)->resolveBankName($paper) }}</div>
            </div>
          </div>
        </div>
        <div class="card card-static card-body" style="padding:1rem">
          <div style="font-weight:600;font-family:var(--fu);margin-bottom:.35rem">Admin Review Step</div>
          <div class="text-muted" style="font-size:.84rem;line-height:1.6;margin-bottom:1rem">
            Save the exam metadata first. When the title, year, subject, PDF mode, and answer-key mode are ready, click parse. The parser will create exam questions and sync the same questions with answers into the reusable question bank.
          </div>
          <form action="{{ route('admin.exams.parse', $paper) }}" method="POST" id="parse-paper-form">
            @csrf
            <button
              type="submit"
              class="btn btn-primary"
              id="parse-paper-button"
              data-status-url="{{ route('admin.papers.parse-status', $paper) }}"
              @disabled(!$paper->original_file && !$paper->source_url || $paper->parse_status === 'processing' || ($paper->parse_status === 'pending' && str_contains((string) $paper->parse_log, 'Queued')))
            >
              {{ $paper->parse_status === 'processing' || ($paper->parse_status === 'pending' && str_contains((string) $paper->parse_log, 'Queued')) ? 'Parsing In Progress' : 'Parse Now' }}
            </button>
          </form>
          <div id="parse-help-text" class="text-muted" style="font-size:.78rem;margin-top:.65rem">
            @if($paper->parse_status === 'processing' || ($paper->parse_status === 'pending' && str_contains((string) $paper->parse_log, 'Queued')))
              Parsing is already running. Please wait for it to finish before trying again.
            @else
              Question order is already randomized during exam attempts.
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Publish Readiness</div>
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap">
        <div>
          <div style="font-weight:700;color:{{ ($publishReadiness['ready'] ?? false) ? 'var(--ok)' : 'var(--err)' }}">
            {{ ($publishReadiness['ready'] ?? false) ? 'Ready to approve' : 'Needs fixes before approval' }}
          </div>
          <div class="text-muted" style="font-size:.84rem;margin-top:.35rem">
            Questions: {{ number_format($publishReadiness['question_count'] ?? 0) }} · Incomplete: {{ number_format($publishReadiness['invalid_questions'] ?? 0) }}
          </div>
        </div>
        @if(!empty($publishReadiness['issues']))
        <div style="display:grid;gap:.3rem">
          @foreach($publishReadiness['issues'] as $issue)
          <div class="text-muted" style="font-size:.84rem">• {{ $issue }}</div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>

  @php $showLegacyTao = $paper->tao_test_id || $paper->tao_delivery_id || $paper->tao_last_error || $paper->taoSyncLogs->count(); @endphp
  @if($showLegacyTao)
  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Legacy TAO Sync</div>
    <div class="card-body" style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap">
      <div>
        <div class="text-muted" style="font-size:.82rem">Sync Status</div>
        <div style="font-weight:600;margin-top:.25rem">{{ ucfirst($paper->tao_sync_status ?? 'pending') }}</div>
        <div class="text-muted" style="font-size:.82rem;margin-top:.5rem">TAO Test ID: {{ $paper->tao_test_id ?: 'Not synced yet' }}</div>
        <div class="text-muted" style="font-size:.82rem">TAO Delivery ID: {{ $paper->tao_delivery_id ?: 'Not created yet' }}</div>
        @if($paper->tao_last_error)
          <div class="alert alert-error mt-2" style="font-size:.82rem">{{ $paper->tao_last_error }}</div>
        @endif
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <form action="{{ route('admin.exams.sync-tao', $paper) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-outline btn-sm">Run Legacy Sync</button>
        </form>
      </div>
    </div>
  </div>
  @endif

  @if($showLegacyTao)
  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Legacy Sync History</div>
    <div class="card-body" style="padding:0">
      @if($paper->taoSyncLogs->count())
        <div class="tbl-wrap" style="margin:0">
          <table class="tbl">
            <thead>
              <tr>
                <th>When</th>
                <th>By</th>
                <th>Trigger</th>
                <th>Status</th>
                <th>Message</th>
              </tr>
            </thead>
            <tbody>
              @foreach($paper->taoSyncLogs->take(10) as $log)
                <tr>
                  <td class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                  <td>{{ $log->user->name ?? 'System' }}</td>
                  <td><span class="badge badge-gray">{{ ucfirst($log->trigger) }}</span></td>
                  <td><span class="badge {{ $log->status === 'success' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($log->status) }}</span></td>
                  <td>
                    <div>{{ $log->message ?: '—' }}</div>
                    @if($log->tao_test_id || $log->tao_delivery_id)
                      <div class="text-muted" style="font-size:.78rem;margin-top:.25rem">
                        Test: {{ $log->tao_test_id ?: '—' }} · Delivery: {{ $log->tao_delivery_id ?: '—' }}
                      </div>
                    @endif
                    @if(!empty($log->request_payload) || !empty($log->response_payload))
                      <details style="margin-top:.5rem">
                        <summary style="cursor:pointer;color:var(--teal);font-size:.82rem;font-family:var(--fu)">View full payload</summary>
                        <div style="display:grid;grid-template-columns:1fr;gap:.5rem;margin-top:.6rem">
                          @if(!empty($log->request_payload))
                            <div>
                              <div class="text-muted" style="font-size:.75rem;margin-bottom:.2rem">Request</div>
                              <pre style="margin:0;white-space:pre-wrap;word-break:break-word;background:var(--border-l);padding:.75rem;border-radius:var(--r1);font-size:.76rem;line-height:1.5">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                          @endif
                          @if(!empty($log->response_payload))
                            <div>
                              <div class="text-muted" style="font-size:.75rem;margin-bottom:.2rem">Response</div>
                              <pre style="margin:0;white-space:pre-wrap;word-break:break-word;background:var(--border-l);padding:.75rem;border-radius:var(--r1);font-size:.76rem;line-height:1.5">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                          @endif
                        </div>
                      </details>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div style="padding:1rem 1.25rem" class="text-muted">No TAO sync attempts have been recorded for this exam yet.</div>
      @endif
    </div>
  </div>
  @endif

  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Attempt Integrity Review</div>
    <div class="card-body" style="padding:0">
      @php $reviewAttempts = $paper->attempts->filter(fn($attempt) => !empty($attempt->anti_cheat_review)); @endphp
      @if($reviewAttempts->count())
      <div class="tbl-wrap" style="margin:0">
        <table class="tbl">
          <thead>
            <tr>
              <th>Student</th>
              <th>Submitted</th>
              <th>Score</th>
              <th>Risk</th>
              <th>Alerts</th>
            </tr>
          </thead>
          <tbody>
            @foreach($reviewAttempts as $attempt)
            @php $review = $attempt->anti_cheat_review ?? []; @endphp
            <tr>
              <td>{{ $attempt->student->name ?? 'Student' }}</td>
              <td class="text-muted">{{ $attempt->submitted_at?->format('d M Y, h:i A') ?: '—' }}</td>
              <td>{{ number_format($attempt->percentage ?? 0, 2) }}%</td>
              <td><span class="badge {{ ($review['risk_level'] ?? 'low') === 'high' ? 'badge-red' : (($review['risk_level'] ?? 'low') === 'medium' ? 'badge-gold' : 'badge-green') }}">{{ ucfirst($review['risk_level'] ?? 'low') }}</span></td>
              <td>
                @if(!empty($review['alerts']))
                  <div style="display:flex;flex-direction:column;gap:.25rem">
                    @foreach($review['alerts'] as $alert)
                    <span class="text-muted" style="font-size:.8rem">{{ $alert }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-muted">No alerts</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div style="padding:1rem 1.25rem" class="text-muted">No attempt integrity reviews are available for this exam yet.</div>
      @endif
    </div>
  </div>

  @if(isset($questionBankItems) && $questionBankItems->isNotEmpty())
    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Import From Question Bank</div>
    <div class="card-body">
      <div class="text-muted" style="font-size:.84rem;margin-bottom:1rem">Pull reusable questions into this exam without leaving the editor. We’re biasing the suggestions toward this exam’s category and subject.</div>
      <form action="{{ route('admin.exams.import-question-bank', $paper) }}" method="POST">
        @csrf
        <div class="g-grid" style="grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:1rem">
          <div>
            <label class="form-label">Search</label>
            <input type="text" class="form-control" id="question-bank-search" placeholder="Search text, subject, topic, section">
          </div>
          <div>
            <label class="form-label">Type</label>
            <select class="form-control" id="question-bank-type-filter">
              <option value="">All types</option>
              @foreach($questionBankItems->pluck('question_type')->filter()->unique()->sort()->values() as $type)
              <option value="{{ strtolower($type) }}">{{ strtoupper($type) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">Difficulty</label>
            <select class="form-control" id="question-bank-difficulty-filter">
              <option value="">All difficulty</option>
              @foreach($questionBankItems->pluck('difficulty')->filter()->unique()->sort()->values() as $difficulty)
              <option value="{{ strtolower($difficulty) }}">{{ ucfirst($difficulty) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">Subject</label>
            <select class="form-control" id="question-bank-subject-filter">
              <option value="">All subjects</option>
              @foreach($questionBankItems->pluck('subject')->filter()->unique()->sort()->values() as $subject)
              <option value="{{ strtolower($subject) }}">{{ $subject }}</option>
              @endforeach
            </select>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" id="question-bank-clear-filters">Clear</button>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
          <label style="display:flex;align-items:center;gap:.55rem">
            <input type="checkbox" id="question-bank-select-all">
            <span>Select all visible</span>
          </label>
          <div class="text-muted" style="font-size:.82rem"><span id="question-bank-visible-count">{{ $questionBankItems->count() }}</span> question(s) visible</div>
        </div>
        <div style="display:grid;gap:.75rem" id="question-bank-import-list">
          @foreach($questionBankItems as $item)
          <label
            data-bank-item
            data-search="{{ strtolower(trim(($item->question_text ?? '').' '.($item->subject ?? '').' '.($item->section ?? '').' '.($item->topic ?? ''))) }}"
            data-type="{{ strtolower($item->question_type ?? '') }}"
            data-difficulty="{{ strtolower($item->difficulty ?? '') }}"
            data-subject="{{ strtolower($item->subject ?? '') }}"
            style="display:flex;gap:.85rem;align-items:flex-start;padding:.85rem 1rem;border:1px solid var(--border-l);border-radius:14px;background:#fff"
          >
            <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" style="margin-top:.15rem">
            <div style="flex:1">
              <div style="font-size:.78rem;color:var(--ink-l);margin-bottom:.25rem">
                {{ $item->category->name ?? 'Uncategorized' }} · {{ strtoupper($item->question_type) }} · {{ ucfirst($item->difficulty) }}
              </div>
              <div style="font-weight:600;font-family:var(--fu)">{{ \Illuminate\Support\Str::limit($item->question_text, 150) }}</div>
              <div class="text-muted" style="font-size:.82rem;margin-top:.25rem">
                {{ collect([$item->subject, $item->section, $item->topic])->filter()->join(' · ') ?: 'No subject metadata yet' }}
              </div>
              @if($item->interaction_type || $item->qti_identifier)
              <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.45rem">
                @if($item->interaction_type)
                <span class="badge badge-gray">{{ $item->interaction_type }}</span>
                @endif
                @if($item->qti_identifier)
                <span class="badge badge-gold">{{ $item->qti_identifier }}</span>
                @endif
              </div>
              @endif
            </div>
            <div style="text-align:right;font-size:.82rem">
              <div>{{ number_format((float) $item->marks, 2) }} marks</div>
              <div class="text-muted">-{{ number_format((float) $item->negative_marking, 2) }}</div>
            </div>
          </label>
          @endforeach
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:1rem">
          <button type="submit" class="btn btn-outline">Import Selected Questions</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  <form action="{{ route('admin.exams.update',$paper) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Information</div>
      <div class="card-body">
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title',$paper->title) }}" required></div>
        <div class="g-grid" style="grid-template-columns:1fr 180px;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject',$paper->subject) }}"></div>
          <div class="form-group" style="margin:0"><label class="form-label">Year</label><input type="number" name="exam_year" class="form-control" value="{{ old('exam_year',$paper->exam_year) }}" min="1900" max="2100" placeholder="2025"></div>
        </div>
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control" required>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id',$paper->category_id)==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Language</label>
            <select name="language" class="form-control">
              @foreach(['English','Hindi','Both'] as $lang)
                <option value="{{ $lang }}" {{ old('language',$paper->language)===$lang?'selected':'' }}>{{ $lang }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">Exam Type</label>
            <select name="exam_type" class="form-control">
              <option value="mock" {{ old('exam_type',$paper->exam_type)==='mock'?'selected':'' }}>Mock Exam Paper</option>
              <option value="previous_year" {{ old('exam_type',$paper->exam_type)==='previous_year'?'selected':'' }}>Old Exam Paper (PYQ)</option>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              @foreach(['draft','pending_review','approved','rejected'] as $status)
                <option value="{{ $status }}" {{ old('status',$paper->status)===$status?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description',$paper->description) }}</textarea></div>
        <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" value="{{ old('tags',implode(', ',$paper->tags??[])) }}"></div>
      </div>
    </div>

    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Exam Settings</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Duration (min)</label><input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes',$paper->duration_minutes) }}" min="10" required></div>
          <div class="form-group" style="margin:0"><label class="form-label">Total Marks</label><input type="number" name="max_marks" class="form-control" value="{{ old('max_marks',$paper->max_marks) }}" min="10" required></div>
          <div class="form-group" style="margin:0"><label class="form-label">Negative Marking</label><input type="number" name="negative_marking" class="form-control" value="{{ old('negative_marking',$paper->negative_marking) }}" step="0.25" min="0"></div>
        </div>
        <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Difficulty</label><select name="difficulty" class="form-control"><option value="easy" {{ old('difficulty',$paper->difficulty)==='easy'?'selected':'' }}>Easy</option><option value="medium" {{ old('difficulty',$paper->difficulty)==='medium'?'selected':'' }}>Medium</option><option value="hard" {{ old('difficulty',$paper->difficulty)==='hard'?'selected':'' }}>Hard</option></select></div>
          <div class="form-group" style="margin:0"><label class="form-label">Max Retakes</label><input type="number" name="max_retakes" class="form-control" value="{{ old('max_retakes',$paper->max_retakes) }}" min="1" max="10"></div>
        </div>
        <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">PDF Kind</label>
            <select name="pdf_kind" id="answer-pdf-kind" class="form-control">
              <option value="text" {{ old('pdf_kind', $paper->pdf_kind ?? 'text') === 'text' ? 'selected' : '' }}>Text PDF</option>
              <option value="scanned" {{ old('pdf_kind', $paper->pdf_kind) === 'scanned' ? 'selected' : '' }}>Scanned PDF (OCR)</option>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Answer Key Source</label>
            <select name="answer_key_mode" id="answer_key_mode" class="form-control">
              <option value="same_pdf" {{ old('answer_key_mode', $paper->answer_key_mode ?? 'same_pdf') === 'same_pdf' ? 'selected' : '' }}>Same PDF</option>
              <option value="separate_pdf" {{ old('answer_key_mode', $paper->answer_key_mode) === 'separate_pdf' ? 'selected' : '' }}>Separate PDF</option>
              <option value="none" {{ old('answer_key_mode', $paper->answer_key_mode) === 'none' ? 'selected' : '' }}>No answer key</option>
            </select>
          </div>
        </div>
        <div class="form-group mt-2" style="margin-bottom:0">
          <label class="form-label">Exam Sections</label>
          <textarea name="exam_sections_text" class="form-control" rows="4" placeholder="General Awareness: Static GK and current affairs&#10;Quantitative Aptitude: Arithmetic, algebra, data interpretation">{{ old('exam_sections_text', collect($paper->exam_sections ?? [])->map(fn($section) => ($section['name'] ?? '').(($section['description'] ?? null) ? ': '.($section['description']) : ''))->implode("\n")) }}</textarea>
          <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">Optional. One line per section in <code>Section Name: Short description</code> format.</div>
        </div>
        <div class="form-group mt-2" style="margin-bottom:0">
          <label class="form-label">Section Timer Rules</label>
          <textarea name="section_time_rules_text" class="form-control" rows="4" placeholder="General: 20&#10;Quantitative Aptitude: 30">{{ old('section_time_rules_text', collect($paper->section_time_rules ?? [])->map(fn($rule) => ($rule['section'] ?? '').': '.($rule['minutes'] ?? ''))->implode("\n")) }}</textarea>
          <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">Optional. One line per section in <code>Section Name: Minutes</code> format. This drives section timer guidance inside the exam runner.</div>
        </div>
        <div class="form-group mt-2" style="margin-bottom:0">
          <label class="form-label">Section Negative Marking Rules</label>
          <textarea name="section_negative_rules_text" class="form-control" rows="4" placeholder="General: 0.25&#10;Quantitative Aptitude: 0.50">{{ old('section_negative_rules_text', collect($paper->section_negative_rules ?? [])->map(fn($rule) => ($rule['section'] ?? '').': '.($rule['negative_marking'] ?? ''))->implode("\n")) }}</textarea>
          <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">Optional. Override negative marking per section with <code>Section Name: Penalty Ratio</code>.</div>
        </div>
        <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">Interoperability Profile</label>
            <select name="interoperability_profile" class="form-control">
              <option value="">None</option>
              @foreach(['qti_foundation','lti_candidate','api_exchange'] as $profile)
              <option value="{{ $profile }}" {{ old('interoperability_profile', $paper->interoperability_profile) === $profile ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $profile)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">QTI / Standards Metadata</label>
            <textarea name="qti_metadata_text" class="form-control" rows="4" placeholder="manifest_identifier: nd-assessment-001&#10;tool_vendor: Naukaridarpan">{{ old('qti_metadata_text', collect($paper->qti_metadata ?? [])->map(fn($value, $key) => $key.': '.$value)->implode("\n")) }}</textarea>
          </div>
        </div>
        <div class="form-group mt-2" style="margin-bottom:0" id="answer-key-url-group">
          <label class="form-label">Answer Key PDF URL</label>
          <input type="url" name="answer_key_pdf_url" class="form-control" value="{{ old('answer_key_pdf_url', $paper->answer_key_pdf_url) }}" placeholder="https://upsc.gov.in/.../answer-key.pdf">
          <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">
            Use this only when the answer key lives in a separate PDF.
            @if($paper->answer_key_applied_at)
              <br>Last applied: {{ $paper->answer_key_applied_at->format('d M Y, h:i A') }}.
            @endif
            @if($paper->answer_key_parse_log)
              <br>{{ $paper->answer_key_parse_log }}
            @endif
          </div>
        </div>
      </div>
    </div>

    @php $questions = $paper->questions_data ? json_decode($paper->questions_data, true) : []; @endphp
    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Question Builder</div>
      <div class="card-body">
        @if(!empty($questions))
          <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem;flex-wrap:wrap">
            <div class="text-muted" style="font-size:.84rem">Review parsed questions here before students see them. You can reorder, remove, or add fresh questions.</div>
            <button type="button" class="btn btn-outline btn-sm" id="add-question-btn">+ Add Question</button>
          </div>
          <div style="display:flex;flex-direction:column;gap:1rem" id="question-editors">
            @foreach($questions as $index => $question)
            <details data-question-editor style="border:1px solid var(--border-l);border-radius:var(--r2);background:#fff" {{ $index === 0 ? 'open' : '' }}>
              <summary class="question-editor-summary" style="padding:1rem 1.1rem;cursor:pointer;font-weight:600;display:flex;justify-content:space-between;gap:1rem;align-items:center">
                <span>Q{{ $question['serial'] ?? ($index + 1) }} · {{ strtoupper($question['type'] ?? 'MCQ') }}</span>
                <span class="text-muted" style="font-size:.82rem">{{ \Illuminate\Support\Str::limit($question['text'] ?? '', 70) }}</span>
              </summary>
              <div style="padding:0 1.1rem 1.1rem">
                <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap">
                  <button type="button" class="btn btn-ghost btn-sm" data-move-question="up">Move Up</button>
                  <button type="button" class="btn btn-ghost btn-sm" data-move-question="down">Move Down</button>
                  <button type="button" class="btn btn-outline btn-sm" data-remove-question>Remove</button>
                </div>
                <div class="g-grid" style="grid-template-columns:110px 140px 120px;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Serial</label>
                    <input type="number" name="questions[{{ $index }}][serial]" class="form-control" value="{{ old("questions.$index.serial", $question['serial'] ?? ($index + 1)) }}" data-question-serial>
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Type</label>
                    <select name="questions[{{ $index }}][type]" class="form-control">
                      @foreach(['mcq','msq','fill_blank','short_answer','long_answer','math','omr'] as $type)
                        <option value="{{ $type }}" {{ old("questions.$index.type", $question['type'] ?? 'mcq') === $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Marks</label>
                    <input type="number" step="0.25" name="questions[{{ $index }}][marks]" class="form-control" value="{{ old("questions.$index.marks", $question['marks'] ?? 1) }}">
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Question Text</label>
                  <textarea name="questions[{{ $index }}][text]" class="form-control" rows="4">{{ old("questions.$index.text", $question['text'] ?? '') }}</textarea>
                </div>

                <div class="g-grid" style="grid-template-columns:1fr 1fr 1fr;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Section</label>
                    <input type="text" name="questions[{{ $index }}][section]" class="form-control" value="{{ old("questions.$index.section", $question['section'] ?? '') }}">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Topic</label>
                    <input type="text" name="questions[{{ $index }}][topic]" class="form-control" value="{{ old("questions.$index.topic", $question['topic'] ?? '') }}">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Subject</label>
                    <input type="text" name="questions[{{ $index }}][subject]" class="form-control" value="{{ old("questions.$index.subject", $question['subject'] ?? ($paper->subject ?? '')) }}">
                  </div>
                </div>
                <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Interaction Type</label>
                    <input type="text" name="questions[{{ $index }}][interaction_type]" class="form-control" value="{{ old("questions.$index.interaction_type", $question['interaction_type'] ?? '') }}" placeholder="choiceInteraction, textEntryInteraction">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">QTI Identifier</label>
                    <input type="text" name="questions[{{ $index }}][qti_identifier]" class="form-control" value="{{ old("questions.$index.qti_identifier", $question['qti_identifier'] ?? '') }}" placeholder="item-identifier-001">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Advanced Metadata</label>
                  <textarea name="questions[{{ $index }}][advanced_metadata_text]" class="form-control" rows="3" placeholder="responseCardinality: single&#10;shuffle: true">{{ old("questions.$index.advanced_metadata_text", collect($question['advanced_metadata'] ?? [])->map(fn ($value, $key) => $key.': '.$value)->implode("\n")) }}</textarea>
                </div>

                @php
                  $correct = $question['correct_answer'] ?? null;
                  $correctValue = is_array($correct) ? implode(', ', $correct) : $correct;
                @endphp
                <div class="form-group">
                  <label class="form-label">Correct Answer</label>
                  <input type="text" name="questions[{{ $index }}][correct_answer]" class="form-control" value="{{ old("questions.$index.correct_answer", $correctValue) }}">
                  <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">For MSQ, use comma-separated option labels like <code>A, C</code>.</div>
                </div>

                @if(!empty($question['options']))
                <div style="display:flex;flex-direction:column;gap:.65rem;margin-bottom:1rem">
                  <div class="form-label" style="margin:0">Options</div>
                  @foreach($question['options'] as $optionIndex => $option)
                  <div class="g-grid" style="grid-template-columns:90px 1fr;gap:.75rem">
                    <input type="text" name="questions[{{ $index }}][options][{{ $optionIndex }}][label]" class="form-control" value="{{ old("questions.$index.options.$optionIndex.label", $option['label'] ?? '') }}">
                    <input type="text" name="questions[{{ $index }}][options][{{ $optionIndex }}][text]" class="form-control" value="{{ old("questions.$index.options.$optionIndex.text", $option['text'] ?? '') }}">
                  </div>
                  @endforeach
                </div>
                @endif

                <div class="form-group" style="margin-bottom:0">
                  <label class="form-label">Explanation</label>
                  <textarea name="questions[{{ $index }}][explanation]" class="form-control" rows="3">{{ old("questions.$index.explanation", $question['explanation'] ?? '') }}</textarea>
                </div>
              </div>
            </details>
            @endforeach
          </div>
          <template id="question-editor-template">
            <details data-question-editor open style="border:1px solid var(--border-l);border-radius:var(--r2);background:#fff">
              <summary class="question-editor-summary" style="padding:1rem 1.1rem;cursor:pointer;font-weight:600;display:flex;justify-content:space-between;gap:1rem;align-items:center">
                <span>New Question</span>
                <span class="text-muted" style="font-size:.82rem">Add the prompt and answer details</span>
              </summary>
              <div style="padding:0 1.1rem 1.1rem">
                <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap">
                  <button type="button" class="btn btn-ghost btn-sm" data-move-question="up">Move Up</button>
                  <button type="button" class="btn btn-ghost btn-sm" data-move-question="down">Move Down</button>
                  <button type="button" class="btn btn-outline btn-sm" data-remove-question>Remove</button>
                </div>
                <div class="g-grid" style="grid-template-columns:110px 140px 120px;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Serial</label>
                    <input type="number" class="form-control" data-field="serial" data-question-serial value="1">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Type</label>
                    <select class="form-control" data-field="type">
                      @foreach(['mcq','msq','fill_blank','short_answer','long_answer','math','omr'] as $type)
                      <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Marks</label>
                    <input type="number" step="0.25" class="form-control" data-field="marks" value="1">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Question Text</label>
                  <textarea class="form-control" rows="4" data-field="text"></textarea>
                </div>
                <div class="g-grid" style="grid-template-columns:1fr 1fr 1fr;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Section</label>
                    <input type="text" class="form-control" data-field="section">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Topic</label>
                    <input type="text" class="form-control" data-field="topic">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" data-field="subject" value="{{ $paper->subject }}">
                  </div>
                </div>
                <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
                  <div class="form-group" style="margin:0">
                    <label class="form-label">Interaction Type</label>
                    <input type="text" class="form-control" data-field="interaction_type" placeholder="choiceInteraction">
                  </div>
                  <div class="form-group" style="margin:0">
                    <label class="form-label">QTI Identifier</label>
                    <input type="text" class="form-control" data-field="qti_identifier" placeholder="item-identifier-001">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Advanced Metadata</label>
                  <textarea class="form-control" rows="3" data-field="advanced_metadata_text" placeholder="responseCardinality: single&#10;shuffle: true"></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label">Correct Answer</label>
                  <input type="text" class="form-control" data-field="correct_answer">
                </div>
                <div style="display:flex;flex-direction:column;gap:.65rem;margin-bottom:1rem">
                  <div class="form-label" style="margin:0">Options</div>
                  @foreach(range(0,3) as $optionIndex)
                  <div class="g-grid" style="grid-template-columns:90px 1fr;gap:.75rem">
                    <input type="text" class="form-control" data-field="options.{{ $optionIndex }}.label" value="{{ chr(65 + $optionIndex) }}">
                    <input type="text" class="form-control" data-field="options.{{ $optionIndex }}.text">
                  </div>
                  @endforeach
                </div>
                <div class="form-group" style="margin-bottom:0">
                  <label class="form-label">Explanation</label>
                  <textarea class="form-control" rows="3" data-field="explanation"></textarea>
                </div>
              </div>
            </details>
          </template>
        @else
          <div class="text-muted">No parsed questions are stored for this exam yet. Finish parsing first, then come back to edit them here.</div>
        @endif
      </div>
    </div>

    <div class="card card-static mb-4">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Pricing</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Seller Price (₹)</label><input type="number" name="seller_price" class="form-control" value="{{ old('seller_price',$paper->seller_price) }}" min="0"></div>
          <div class="form-group" style="margin:0"><label class="form-label">Free Access</label><select name="is_free" class="form-control"><option value="0" {{ !old('is_free',$paper->is_free)?'selected':'' }}>No</option><option value="1" {{ old('is_free',$paper->is_free)?'selected':'' }}>Yes</option></select></div>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
      <button type="submit" class="btn btn-primary">Save Exam</button>
      @if($paper->status !== 'approved')
      <button type="submit" form="delete-exam-form" class="btn btn-ghost" style="color:var(--err)" onclick="return confirm('Delete this exam? This cannot be undone.');">Delete Exam</button>
      @endif
    </div>
  </form>
  @if($paper->status !== 'approved')
  <form id="delete-exam-form" method="POST" action="{{ route('admin.exams.destroy', $paper) }}">
    @csrf
    @method('DELETE')
  </form>
  @endif
    </main>
  </div>
</div>
@push('scripts')
<script>
const questionEditorsContainer = document.getElementById('question-editors');
const addQuestionBtn = document.getElementById('add-question-btn');
const questionEditorTemplate = document.getElementById('question-editor-template');
const questionBankSearch = document.getElementById('question-bank-search');
const questionBankTypeFilter = document.getElementById('question-bank-type-filter');
const questionBankDifficultyFilter = document.getElementById('question-bank-difficulty-filter');
const questionBankSubjectFilter = document.getElementById('question-bank-subject-filter');
const questionBankSelectAll = document.getElementById('question-bank-select-all');
const questionBankClearFilters = document.getElementById('question-bank-clear-filters');
const questionBankVisibleCount = document.getElementById('question-bank-visible-count');
const parsePaperButton = document.getElementById('parse-paper-button');
const parseStatusLabel = document.getElementById('parse-status-label');
const parseProgressBar = document.getElementById('parse-progress-bar');
const parseProgressNote = document.getElementById('parse-progress-note');
const parseLogText = document.getElementById('parse-log-text');
const parseHelpText = document.getElementById('parse-help-text');

function prettyStatus(status) {
  return String(status || 'pending').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

function applyParseState(payload) {
  if (!payload) return;
  const status = payload.status || 'pending';
  const progress = Number(payload.progress || 0);
  const running = status === 'processing' || (status === 'pending' && String(payload.log || '').includes('Queued'));

  if (parseStatusLabel) parseStatusLabel.textContent = prettyStatus(status);
  if (parseProgressBar) {
    parseProgressBar.style.width = `${progress}%`;
    parseProgressBar.style.background = status === 'failed'
      ? 'var(--err)'
      : 'linear-gradient(90deg, var(--teal), #79c8ff)';
  }
  if (parseProgressNote) {
    parseProgressNote.textContent = status === 'failed'
      ? 'Parsing stopped with an error'
      : `${progress}% complete`;
  }
  if (parseLogText) parseLogText.textContent = payload.log || '';
  if (parsePaperButton) {
    parsePaperButton.disabled = running;
    parsePaperButton.textContent = running ? 'Parsing In Progress' : 'Parse Now';
  }
  if (parseHelpText) {
    parseHelpText.textContent = running
      ? 'Parsing is already running. We are refreshing this status automatically.'
      : (status === 'failed'
          ? 'Parsing failed. Fix the issue above and click Parse Now again when ready.'
          : 'Question order is already randomized during exam attempts.');
  }
}

async function pollParseStatus() {
  if (!parsePaperButton?.dataset.statusUrl) return;
  try {
    const response = await fetch(parsePaperButton.dataset.statusUrl, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    });
    if (!response.ok) return;
    const payload = await response.json();
    applyParseState(payload);
    if (payload.status === 'processing' || (payload.status === 'pending' && String(payload.log || '').includes('Queued'))) {
      window.setTimeout(pollParseStatus, 4000);
    }
  } catch (error) {
    // Keep the existing status visible; the admin can refresh manually if needed.
  }
}

function getQuestionEditors() {
  return Array.from(document.querySelectorAll('[data-question-editor]'));
}

function getBankItems() {
  return Array.from(document.querySelectorAll('[data-bank-item]'));
}

function applyQuestionBankFilters() {
  const search = (questionBankSearch?.value || '').trim().toLowerCase();
  const type = (questionBankTypeFilter?.value || '').trim().toLowerCase();
  const difficulty = (questionBankDifficultyFilter?.value || '').trim().toLowerCase();
  const subject = (questionBankSubjectFilter?.value || '').trim().toLowerCase();

  let visibleCount = 0;

  getBankItems().forEach((item) => {
    const matchesSearch = !search || (item.dataset.search || '').includes(search);
    const matchesType = !type || (item.dataset.type || '') === type;
    const matchesDifficulty = !difficulty || (item.dataset.difficulty || '') === difficulty;
    const matchesSubject = !subject || (item.dataset.subject || '') === subject;
    const visible = matchesSearch && matchesType && matchesDifficulty && matchesSubject;

    item.style.display = visible ? 'flex' : 'none';
    if (visible) visibleCount += 1;
  });

  if (questionBankVisibleCount) questionBankVisibleCount.textContent = String(visibleCount);

  if (questionBankSelectAll) {
    const visibleItems = getBankItems().filter((item) => item.style.display !== 'none');
    questionBankSelectAll.checked = visibleItems.length > 0 && visibleItems.every((item) => item.querySelector('input[type="checkbox"]')?.checked);
  }
}

function refreshQuestionEditorNames() {
  getQuestionEditors().forEach((editor, index) => {
    const serialField = editor.querySelector('[data-question-serial]');
    if (serialField) serialField.value = index + 1;

    const typeField = editor.querySelector('select');
    const textField = editor.querySelector('textarea');
    const summaryParts = editor.querySelectorAll('.question-editor-summary span');
    if (summaryParts[0]) summaryParts[0].textContent = `Q${index + 1} · ${String(typeField?.value || 'mcq').toUpperCase()}`;
    if (summaryParts[1]) summaryParts[1].textContent = (textField?.value || 'Add the prompt and answer details').slice(0, 70);

    editor.querySelectorAll('input, textarea, select').forEach((field) => {
      const dataField = field.dataset.field;
      if (dataField) {
        field.name = `questions[${index}][${dataField.replaceAll('.', '][')}]`;
      } else if (field.name) {
        field.name = field.name.replace(/questions\[\d+\]/, `questions[${index}]`);
      }
    });
  });
}

function bindQuestionEditor(editor) {
  editor.querySelector('[data-remove-question]')?.addEventListener('click', () => {
    editor.remove();
    refreshQuestionEditorNames();
  });

  editor.querySelectorAll('[data-move-question]').forEach((button) => {
    button.addEventListener('click', () => {
      const direction = button.getAttribute('data-move-question');
      const sibling = direction === 'up' ? editor.previousElementSibling : editor.nextElementSibling;
      if (!sibling) return;
      if (direction === 'up') {
        editor.parentNode.insertBefore(editor, sibling);
      } else {
        editor.parentNode.insertBefore(sibling, editor);
      }
      refreshQuestionEditorNames();
    });
  });

  editor.querySelectorAll('textarea, select').forEach((field) => {
    field.addEventListener('input', refreshQuestionEditorNames);
    field.addEventListener('change', refreshQuestionEditorNames);
  });
}

getQuestionEditors().forEach(bindQuestionEditor);
refreshQuestionEditorNames();

addQuestionBtn?.addEventListener('click', () => {
  if (!questionEditorsContainer || !questionEditorTemplate) return;
  const newEditor = questionEditorTemplate.content.firstElementChild.cloneNode(true);
  questionEditorsContainer.appendChild(newEditor);
  bindQuestionEditor(newEditor);
  refreshQuestionEditorNames();
});

[questionBankSearch, questionBankTypeFilter, questionBankDifficultyFilter, questionBankSubjectFilter].forEach((element) => {
  element?.addEventListener('input', applyQuestionBankFilters);
  element?.addEventListener('change', applyQuestionBankFilters);
});

questionBankSelectAll?.addEventListener('change', () => {
  getBankItems().forEach((item) => {
    if (item.style.display === 'none') return;
    const checkbox = item.querySelector('input[type="checkbox"]');
    if (checkbox) checkbox.checked = questionBankSelectAll.checked;
  });
});

questionBankClearFilters?.addEventListener('click', () => {
  if (questionBankSearch) questionBankSearch.value = '';
  if (questionBankTypeFilter) questionBankTypeFilter.value = '';
  if (questionBankDifficultyFilter) questionBankDifficultyFilter.value = '';
  if (questionBankSubjectFilter) questionBankSubjectFilter.value = '';
  applyQuestionBankFilters();
});

getBankItems().forEach((item) => {
  item.querySelector('input[type="checkbox"]')?.addEventListener('change', applyQuestionBankFilters);
});

function toggleAnswerKeyUrlGroup() {
  const mode = document.getElementById('answer_key_mode')?.value || 'same_pdf';
  const group = document.getElementById('answer-key-url-group');
  if (!group) return;
  group.style.display = mode === 'separate_pdf' ? 'block' : 'none';
}

document.getElementById('answer_key_mode')?.addEventListener('change', toggleAnswerKeyUrlGroup);
toggleAnswerKeyUrlGroup();

applyQuestionBankFilters();
pollParseStatus();
</script>
@endpush
@endsection
