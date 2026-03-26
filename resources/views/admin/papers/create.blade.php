@extends('layouts.app')
@section('title','Admin Upload Paper — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="margin-bottom:1.5rem">
        <h2 class="mb-1">Upload Paper (Admin)</h2>
        <p class="text-muted">Create and publish exams directly from the platform.</p>
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
          <div id="parse-log" style="margin-top:1rem;font-size:.85rem;color:var(--ink-l)">Waiting for parser…</div>
        </div>
      </div>
      @endif
      @if($errors->any())
        <div class="alert alert-error mb-3">{{ $errors->first() }}</div>
      @endif

      <form action="{{ route('admin.papers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Info</div>
          <div class="card-body">
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control"></div>
            <div class="form-group"><label class="form-label">Exam Type</label>
              <select name="exam_type" class="form-control">
                <option value="mock">Mock Exam Paper</option>
                <option value="previous_year">Old Exam Paper (PYQ)</option>
              </select>
            </div>
            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group"><label class="form-label">Category *</label><select name="category_id" class="form-control" required>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
              <div class="form-group"><label class="form-label">Language</label><select name="language" class="form-control"><option>English</option><option>Hindi</option><option>Both</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" placeholder="comma separated"></div>
          </div>
        </div>

        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Exam Settings</div>
          <div class="card-body">
            <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:.75rem">
              <div class="form-group"><label class="form-label">Duration (min)</label><input type="number" name="duration_minutes" class="form-control" value="60" min="10" required></div>
              <div class="form-group"><label class="form-label">Total Marks</label><input type="number" name="max_marks" class="form-control" value="100" min="10" required></div>
              <div class="form-group"><label class="form-label">Negative Marking</label><input type="number" name="negative_marking" class="form-control" value="0" step="0.25" min="0"></div>
            </div>
            <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
              <div class="form-group"><label class="form-label">Difficulty</label><select name="difficulty" class="form-control"><option value="easy">Easy</option><option value="medium" selected>Medium</option><option value="hard">Hard</option></select></div>
              <div class="form-group"><label class="form-label">Max Retakes</label><select name="max_retakes" class="form-control">@for($i=1;$i<=5;$i++)<option value="{{ $i }}" {{ $i==3?'selected':'' }}>{{ $i }}</option>@endfor</select></div>
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
              <select name="input_type" class="form-control">
                <option value="pdf">Upload PDF</option>
                <option value="url">PDF URL</option>
                <option value="typed">Typed</option>
              </select>
            </div>
            <div class="form-group"><label class="form-label">PDF File</label><input type="file" name="pdf_file" class="form-control" accept=".pdf"></div>
            <div class="form-group"><label class="form-label">PDF URL</label><input type="url" name="pdf_url" class="form-control"></div>
            <div class="form-group"><label class="form-label">Typed Content</label><textarea name="typed_content" class="form-control" rows="8"></textarea></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Create Paper</button>
      </form>
    </main>
  </div>
</div>
@if(request('paper_id'))
@push('scripts')
<script>
  const parseId = "{{ request('paper_id') }}";
  const statusEl = document.getElementById('parse-status');
  const qEl = document.getElementById('parse-questions');
  const tEl = document.getElementById('parse-types');
  const logEl = document.getElementById('parse-log');
  async function refreshParse(){
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
@endif
@endsection
