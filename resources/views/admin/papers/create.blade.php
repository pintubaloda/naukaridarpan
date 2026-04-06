@extends('layouts.app')
@section('title','Admin Upload Paper — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="margin-bottom:1.5rem">
        <h2 class="mb-1">Upload Paper (Admin)</h2>
        <p class="text-muted">Save exams from PDF or typed/manual entry as drafts first. Review the metadata in admin, then click parse when you are ready.</p>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem">
          <a href="{{ route('admin.papers.create', ['input_type' => 'typed']) }}" class="btn btn-outline btn-sm">Manual Exam Entry</a>
          <a href="{{ route('admin.papers.create', ['input_type' => 'pdf']) }}" class="btn btn-ghost btn-sm">Upload PDF</a>
          <a href="{{ route('admin.exam-templates.index') }}" class="btn btn-ghost btn-sm">Exam Templates</a>
        </div>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if(request('paper_id'))
      <div class="card card-static mb-3" id="parse-box">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Parsing Status</div>
        <div class="card-body">
          <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center">
            <div>
              <div class="text-muted" style="font-size:.82rem">Status</div>
              <div id="parse-status" style="font-weight:600">pending</div>
            </div>
            <div>
              <div class="text-muted" style="font-size:.82rem">Questions</div>
              <div id="parse-questions" style="font-weight:600">0</div>
            </div>
            <div>
              <div class="text-muted" style="font-size:.82rem">Types</div>
              <div id="parse-types" style="font-weight:600">—</div>
            </div>
          </div>
          <div id="parse-log" style="margin-top:1rem;font-size:.85rem;color:var(--ink-l)">Saved as draft. Open the exam and click Parse when ready.</div>
        </div>
      </div>
      @endif
      @if($errors->any())
        <div class="alert alert-error mb-3">{{ $errors->first() }}</div>
      @endif

      @if($templates->isNotEmpty())
      <div class="card card-static mb-3">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Start From Template</div>
        <div class="card-body">
          <div class="g-grid" style="grid-template-columns:1fr auto;gap:.75rem;align-items:end">
            <div class="form-group" style="margin:0">
              <label class="form-label">Saved Exam Template</label>
              <select id="template-select" class="form-control">
                <option value="">Choose a template</option>
                @foreach($templates as $template)
                <option value="{{ $template->id }}" {{ (string) optional($selectedTemplate)->id === (string) $template->id ? 'selected' : '' }}>
                  {{ $template->name }}{{ $template->category ? ' · '.$template->category->name : '' }}
                </option>
                @endforeach
              </select>
            </div>
            <button type="button" class="btn btn-outline" id="apply-template-btn">Use Template</button>
          </div>
          @if($selectedTemplate)
          <div style="margin-top:1rem;padding:.9rem 1rem;background:var(--paper);border-radius:14px">
            <div style="font-weight:600;font-family:var(--fu)">{{ $selectedTemplate->name }}</div>
            <div class="text-muted" style="font-size:.84rem;margin-top:.25rem">
              {{ $selectedTemplate->description ?: 'This template will prefill category, duration, negative marking, and section structure.' }}
            </div>
            @if(!empty($selectedTemplate->sections))
            <div style="display:grid;gap:.45rem;margin-top:.8rem">
              @foreach($selectedTemplate->sections as $section)
              <div style="padding:.55rem .7rem;background:#fff;border-radius:10px">
                <strong>{{ $section['name'] ?? 'Section' }}</strong>
                @if(!empty($section['notes']))<span class="text-muted"> — {{ $section['notes'] }}</span>@endif
              </div>
              @endforeach
            </div>
            @endif
          </div>
          @endif
        </div>
      </div>
      @endif

      <form action="{{ route('admin.papers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Info</div>
          <div class="card-body">
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title', optional($selectedTemplate)->name) }}" required></div>
            <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject') }}"></div>
            <div class="form-group"><label class="form-label">Exam Year</label><input type="number" name="exam_year" class="form-control" value="{{ old('exam_year') }}" min="1900" max="{{ now()->year + 2 }}"></div>
            <div class="form-group"><label class="form-label">Exam Type</label>
              <select name="exam_type" class="form-control">
                <option value="mock" {{ old('exam_type') === 'mock' ? 'selected' : '' }}>Mock Exam Paper</option>
                <option value="previous_year" {{ old('exam_type') === 'previous_year' ? 'selected' : '' }}>Old Exam Paper (PYQ)</option>
              </select>
            </div>
            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group"><label class="form-label">Category *</label><select name="category_id" class="form-control" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ (string) old('category_id', optional($selectedTemplate)->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div>
              <div class="form-group"><label class="form-label">Language</label><select name="language" class="form-control"><option {{ old('language') === 'English' ? 'selected' : '' }}>English</option><option {{ old('language') === 'Hindi' ? 'selected' : '' }}>Hindi</option><option {{ old('language') === 'Both' ? 'selected' : '' }}>Both</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', optional($selectedTemplate)->description) }}</textarea></div>
            <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" placeholder="comma separated" value="{{ old('tags') }}"></div>
          </div>
        </div>

        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Exam Settings</div>
          <div class="card-body">
            <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:.75rem">
              <div class="form-group"><label class="form-label">Duration (min)</label><input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', optional($selectedTemplate)->duration_minutes ?? 60) }}" min="10" required></div>
              <div class="form-group"><label class="form-label">Total Marks</label><input type="number" name="max_marks" class="form-control" value="100" min="10" required></div>
              <div class="form-group"><label class="form-label">Negative Marking</label><input type="number" name="negative_marking" class="form-control" value="{{ old('negative_marking', optional($selectedTemplate)->default_negative_marking ?? 0) }}" step="0.25" min="0"></div>
            </div>
            <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group" style="margin:0">
                <label class="form-label">PDF Kind</label>
                <select name="pdf_kind" class="form-control">
                  <option value="text" {{ old('pdf_kind', 'text') === 'text' ? 'selected' : '' }}>Text PDF</option>
                  <option value="scanned" {{ old('pdf_kind') === 'scanned' ? 'selected' : '' }}>Scanned PDF</option>
                </select>
              </div>
              <div class="form-group" style="margin:0">
                <label class="form-label">Answer Key Mode</label>
                <select name="answer_key_mode" class="form-control" id="answer-key-mode">
                  <option value="same_pdf" {{ old('answer_key_mode', 'same_pdf') === 'same_pdf' ? 'selected' : '' }}>Same PDF</option>
                  <option value="separate_pdf" {{ old('answer_key_mode') === 'separate_pdf' ? 'selected' : '' }}>Separate PDF</option>
                  <option value="none" {{ old('answer_key_mode') === 'none' ? 'selected' : '' }}>No Answer Key</option>
                </select>
              </div>
            </div>
            <div class="form-group mt-2" id="answer-key-url-group" style="display:none">
              <label class="form-label">Answer Key PDF URL</label>
              <input type="url" name="answer_key_pdf_url" class="form-control" value="{{ old('answer_key_pdf_url') }}">
            </div>
            <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group"><label class="form-label">Difficulty</label><select name="difficulty" class="form-control"><option value="easy">Easy</option><option value="medium" selected>Medium</option><option value="hard">Hard</option></select></div>
              <div class="form-group"><label class="form-label">Max Retakes</label><select name="max_retakes" class="form-control">@for($i=1;$i<=5;$i++)<option value="{{ $i }}" {{ $i==3?'selected':'' }}>{{ $i }}</option>@endfor</select></div>
            </div>
            @if($selectedTemplate && !empty($selectedTemplate->sections))
            <div class="form-group mt-2" style="margin-bottom:0">
              <label class="form-label">Section Blueprint</label>
              <textarea name="exam_sections_text" class="form-control" rows="5">@foreach($selectedTemplate->sections as $section){{ $section['name'] ?? 'Section' }}@if(!empty($section['notes'])): {{ $section['notes'] }}@endif
@endforeach</textarea>
              <div class="form-hint">We’ll save this section structure with the exam so the editor and runner can use it immediately.</div>
            </div>
            @endif
            <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group" style="margin:0">
                <label class="form-label">Interoperability Profile</label>
                <select name="interoperability_profile" class="form-control">
                  <option value="">None</option>
                  @foreach(['qti_foundation','lti_candidate','api_exchange'] as $profile)
                  <option value="{{ $profile }}" {{ old('interoperability_profile') === $profile ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $profile)) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group" style="margin:0">
                <label class="form-label">QTI / Standards Metadata</label>
                <textarea name="qti_metadata_text" class="form-control" rows="4" placeholder="manifest_identifier: nd-assessment-001&#10;tool_vendor: Naukaridarpan">{{ old('qti_metadata_text') }}</textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Pricing</div>
          <div class="card-body">
            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group"><label class="form-label">Seller Price (₹)</label><input type="number" name="seller_price" class="form-control" value="99" min="0" required></div>
              <div class="form-group"><label class="form-label">Make Free</label><select name="is_free" class="form-control"><option value="0">No</option><option value="1">Yes</option></select></div>
            </div>
            <div class="form-group mt-2"><label class="form-check"><input type="checkbox" name="publish_now" value="1"> Publish immediately</label></div>
          </div>
        </div>

        <div class="card card-static mb-4">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Paper Content</div>
          <div class="card-body">
            <div class="form-group"><label class="form-label">Input Type</label>
              @php $selectedInputType = request('input_type', old('input_type', 'pdf')); @endphp
              <select name="input_type" id="paper-input-type" class="form-control">
                <option value="pdf" {{ $selectedInputType === 'pdf' ? 'selected' : '' }}>Upload PDF</option>
                <option value="url" {{ $selectedInputType === 'url' ? 'selected' : '' }}>PDF URL</option>
                <option value="typed" {{ $selectedInputType === 'typed' ? 'selected' : '' }}>Manual / Typed Entry</option>
              </select>
            </div>
            <div class="form-group" id="paper-pdf-file-group"><label class="form-label">PDF File</label><input type="file" name="pdf_file" class="form-control" accept=".pdf"></div>
            <div class="form-group" id="paper-pdf-url-group"><label class="form-label">PDF URL</label><input type="url" name="pdf_url" class="form-control"></div>
            <div class="form-group" id="paper-typed-group">
              <label class="form-label">Typed Content</label>
              <textarea name="typed_content" class="form-control" rows="8" placeholder="Q1. Question text&#10;A. Option 1&#10;B. Option 2&#10;C. Option 3&#10;D. Option 4&#10;Answer: A"></textarea>
              <div class="form-hint">Use this for direct manual paper and exam entry without uploading a file.</div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Save Draft Paper</button>
      </form>
    </main>
  </div>
</div>
@push('scripts')
<script>
function togglePaperInputFields() {
  const selected = document.getElementById('paper-input-type')?.value || 'pdf';
  const pdfFile = document.getElementById('paper-pdf-file-group');
  const pdfUrl = document.getElementById('paper-pdf-url-group');
  const typed = document.getElementById('paper-typed-group');

  if (pdfFile) pdfFile.style.display = selected === 'pdf' ? 'block' : 'none';
  if (pdfUrl) pdfUrl.style.display = selected === 'url' ? 'block' : 'none';
  if (typed) typed.style.display = selected === 'typed' ? 'block' : 'none';
}

function toggleAnswerKeyUrl() {
  const selected = document.getElementById('answer-key-mode')?.value || 'same_pdf';
  const answerKeyUrlGroup = document.getElementById('answer-key-url-group');
  if (answerKeyUrlGroup) answerKeyUrlGroup.style.display = selected === 'separate_pdf' ? 'block' : 'none';
}

document.getElementById('paper-input-type')?.addEventListener('change', togglePaperInputFields);
togglePaperInputFields();
document.getElementById('answer-key-mode')?.addEventListener('change', toggleAnswerKeyUrl);
toggleAnswerKeyUrl();

document.getElementById('apply-template-btn')?.addEventListener('click', () => {
  const templateId = document.getElementById('template-select')?.value;
  const url = new URL(window.location.href);
  if (templateId) {
    url.searchParams.set('template_id', templateId);
    url.searchParams.set('input_type', url.searchParams.get('input_type') || 'typed');
  } else {
    url.searchParams.delete('template_id');
  }
  window.location.href = url.toString();
});

const parseId = "{{ request('paper_id') }}";
const statusEl = document.getElementById('parse-status');
const qEl = document.getElementById('parse-questions');
const tEl = document.getElementById('parse-types');
const logEl = document.getElementById('parse-log');

async function refreshParse(){
  if (!parseId || !statusEl || !qEl || !tEl || !logEl) return;

  try{
    const r = await fetch(`{{ url('/admin/papers') }}/${parseId}/parse-status`, {
      headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      credentials:'same-origin'
    });
    const d = await r.json();
    statusEl.textContent = d.status || 'pending';
    qEl.textContent = d.total_questions ?? 0;
    tEl.textContent = d.question_types ? Object.keys(d.question_types).join(', ') : '—';
    logEl.textContent = d.log || 'Waiting for parser…';
    if (d.status === 'done' || d.status === 'failed') return;
    setTimeout(refreshParse, 5000);
  }catch(e){
    setTimeout(refreshParse, 7000);
  }
}

refreshParse();
</script>
@endpush
@endsection
