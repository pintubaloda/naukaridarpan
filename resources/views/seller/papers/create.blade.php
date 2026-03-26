@extends('layouts.app')
@section('title','Upload Exam Paper — Naukaridarpan')
@section('content')
<div class="container section">
  <div style="max-width:800px;margin:0 auto">
    <div style="margin-bottom:1.5rem">
      <a href="{{ route('seller.papers') }}" style="font-size:.85rem;color:var(--ink-l)">← Back to Papers</a>
      <h2 class="mt-1">Upload New Exam Paper</h2>
      <p style="font-size:.9rem">Upload a PDF or type questions directly. Our AI will parse and convert it automatically.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-3">
      <div>
        <strong>Please fix the following errors:</strong>
        <ul style="margin:.25rem 0 0 1rem;font-size:.88rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    </div>
    @endif

    <form action="{{ route('seller.papers.store') }}" method="POST" enctype="multipart/form-data" id="paper-form">
      @csrf

      {{-- BASIC INFO --}}
      <div class="card card-static mb-3">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">1. Basic Information</div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Exam Title <span style="color:var(--err)">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. SSC CGL 2024 Tier-1 Mock Test — Set 3" required>
          </div>
          <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" placeholder="e.g. Polity, Quant, Reasoning, Current Affairs">
          </div>
          <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
            <div class="form-group" style="margin:0">
              <label class="form-label">Category <span style="color:var(--err)">*</span></label>
              <select name="category_id" class="form-control" required>
                <option value="">Select category…</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Language</label>
              <select name="language" class="form-control">
                <option value="English" {{ old('language')=='English'?'selected':'' }}>English</option>
                <option value="Hindi" {{ old('language')=='Hindi'?'selected':'' }}>Hindi</option>
                <option value="Both" {{ old('language')=='Both'?'selected':'' }}>Both (Bilingual)</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Describe the exam — topics covered, target exam, year of paper, etc.">{{ old('description') }}</textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Tags <span class="form-hint" style="display:inline;margin:0">(comma-separated)</span></label>
            <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="UPSC, GS Paper 1, 2024, History, Geography">
          </div>
        </div>
      </div>

      {{-- EXAM SETTINGS --}}
      <div class="card card-static mb-3">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">2. Exam Settings</div>
        <div class="card-body">
          <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:.75rem">
            <div class="form-group" style="margin:0">
              <label class="form-label">Duration (minutes) *</label>
              <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes',60) }}" min="10" max="360" required>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Total Marks *</label>
              <input type="number" name="max_marks" class="form-control" value="{{ old('max_marks',100) }}" min="10" required>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Negative Marking</label>
              <input type="number" name="negative_marking" class="form-control" value="{{ old('negative_marking',0) }}" min="0" max="1" step="0.25" placeholder="0.25, 0.33, 0.5…">
            </div>
          </div>
          <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
            <div class="form-group" style="margin:0">
              <label class="form-label">Difficulty Level</label>
              <select name="difficulty" class="form-control">
                <option value="easy" {{ old('difficulty')=='easy'?'selected':'' }}>Easy</option>
                <option value="medium" {{ old('difficulty','medium')=='medium'?'selected':'' }}>Medium</option>
                <option value="hard" {{ old('difficulty')=='hard'?'selected':'' }}>Hard</option>
              </select>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Max Retakes per Purchase</label>
              <select name="max_retakes" class="form-control">
                @for($i=1;$i<=5;$i++)<option value="{{ $i }}" {{ old('max_retakes',3)==$i?'selected':'' }}>{{ $i }} attempt(s)</option>@endfor
              </select>
            </div>
          </div>
        </div>
      </div>

      {{-- UPLOAD METHOD --}}
      <div class="card card-static mb-3">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">3. Paper Content</div>
        <div class="card-body">
          <div style="display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap">
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="input_type" value="pdf" {{ old('input_type','pdf')=='pdf'?'checked':'' }} onchange="switchInput(this.value)" style="display:none" id="radio-pdf">
              <div class="method-card" id="mc-pdf" style="border:2px solid var(--saffron);border-radius:var(--r2);padding:1rem;text-align:center;transition:all .15s">
                <div style="font-size:1.8rem;margin-bottom:.4rem">📄</div>
                <div style="font-weight:600;font-size:.9rem;font-family:var(--fu)">Upload PDF</div>
                <div style="font-size:.78rem;color:var(--ink-l);margin-top:.2rem">Best for scanned or digital papers</div>
              </div>
            </label>
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="input_type" value="typed" {{ old('input_type')=='typed'?'checked':'' }} onchange="switchInput(this.value)" style="display:none" id="radio-typed">
              <div class="method-card" id="mc-typed" style="border:2px solid var(--border);border-radius:var(--r2);padding:1rem;text-align:center;transition:all .15s">
                <div style="font-size:1.8rem;margin-bottom:.4rem">⌨️</div>
                <div style="font-weight:600;font-size:.9rem;font-family:var(--fu)">Type Questions</div>
                <div style="font-size:.78rem;color:var(--ink-l);margin-top:.2rem">Enter questions manually with our editor</div>
              </div>
            </label>
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="input_type" value="url" {{ old('input_type')=='url'?'checked':'' }} onchange="switchInput(this.value)" style="display:none" id="radio-url">
              <div class="method-card" id="mc-url" style="border:2px solid var(--border);border-radius:var(--r2);padding:1rem;text-align:center;transition:all .15s">
                <div style="font-size:1.8rem;margin-bottom:.4rem">🔗</div>
                <div style="font-weight:600;font-size:.9rem;font-family:var(--fu)">Paste PDF URL</div>
                <div style="font-size:.78rem;color:var(--ink-l);margin-top:.2rem">We’ll fetch and parse it</div>
              </div>
            </label>
          </div>

          {{-- PDF Upload --}}
          <div id="pdf-section">
            <div class="upload-zone" id="drop-zone" onclick="document.getElementById('pdf-file').click()">
              <div class="upl-icon">📤</div>
              <p><strong style="color:var(--saffron)">Click to upload</strong> or drag and drop your PDF</p>
              <p style="font-size:.8rem;margin-top:.3rem">PDF only · Max 50 MB · Scanned or digital, any quality</p>
            </div>
            <input type="file" id="pdf-file" name="pdf_file" accept=".pdf" style="display:none" onchange="fileChosen(this)">
            <div id="file-name" style="font-size:.85rem;color:var(--ok);margin-top:.5rem;font-family:var(--fu);display:none"></div>
            <div class="alert alert-info mt-2" style="font-size:.85rem">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke-width="2" stroke-linecap="round"/></svg>
              Our AI (Claude) will automatically extract all questions, options, and answers from your PDF. Hindi, English, mixed-language and scanned PDFs are all supported.
            </div>
          </div>

          {{-- Typed content --}}
          <div id="typed-section" style="display:none">
            <div class="alert alert-info mb-2" style="font-size:.85rem">Paste or type your questions below. Use this format:<br><code style="font-size:.8rem">Q1. What is the capital of India?<br>A. Mumbai B. Delhi C. Kolkata D. Chennai<br>Answer: B</code></div>
            <textarea name="typed_content" class="form-control" rows="14" placeholder="Paste your questions here…&#10;&#10;Q1. Question text&#10;A. Option 1&#10;B. Option 2&#10;C. Option 3&#10;D. Option 4&#10;Answer: A&#10;&#10;Q2. Next question…">{{ old('typed_content') }}</textarea>
          </div>

          {{-- PDF URL --}}
          <div id="url-section" style="display:none">
            <div class="form-group">
              <label class="form-label">PDF URL</label>
              <input type="url" name="pdf_url" class="form-control" value="{{ old('pdf_url') }}" placeholder="https://example.com/paper.pdf">
              <div class="form-hint">Publicly accessible PDF link. We’ll download and parse it.</div>
            </div>
          </div>
        </div>
      </div>

      {{-- PRICING --}}
      <div class="card card-static mb-3">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">4. Pricing</div>
        <div class="card-body">
          <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;align-items:end">
            <div class="form-group" style="margin:0">
              <label class="form-label">Your Price (₹) *</label>
              <input type="number" name="seller_price" id="seller-price" class="form-control" value="{{ old('seller_price',99) }}" min="0" step="1" oninput="calcPrice()" required>
              <div class="form-hint">Amount you want to receive per sale (before platform commission)</div>
            </div>
            <div class="form-group" style="margin:0">
              <div id="price-preview" style="background:var(--cream);border:1px solid var(--border);border-radius:var(--r2);padding:1rem">
                <div style="font-size:.78rem;color:var(--ink-l);font-family:var(--fu);margin-bottom:.4rem">Student will pay:</div>
                <div style="font-family:var(--fd);font-size:1.5rem;color:var(--teal)" id="student-price-display">₹114</div>
                <div style="font-size:.75rem;color:var(--ink-l);font-family:var(--fu);margin-top:.2rem">Your price + 15% platform fee</div>
              </div>
            </div>
          </div>
          <div class="form-group mt-2" style="margin-bottom:0">
            <label class="form-check">
              <input type="checkbox" name="is_free" value="1" {{ old('is_free')?'checked':'' }} onchange="toggleFree(this)">
              <span style="font-size:.88rem;color:var(--ink-m)">Make this paper FREE for all students (good for visibility)</span>
            </label>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:1rem;justify-content:flex-end">
        <a href="{{ route('seller.papers') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Upload & Parse Paper →</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function switchInput(val){
  document.getElementById('pdf-section').style.display = val==='pdf'?'block':'none';
  document.getElementById('typed-section').style.display = val==='typed'?'block':'none';
  document.getElementById('url-section').style.display = val==='url'?'block':'none';
  document.getElementById('mc-pdf').style.borderColor  = val==='pdf' ? 'var(--saffron)':'var(--border)';
  document.getElementById('mc-typed').style.borderColor= val==='typed'? 'var(--saffron)':'var(--border)';
  document.getElementById('mc-url').style.borderColor  = val==='url' ? 'var(--saffron)':'var(--border)';
}

function fileChosen(input){
  const fn = document.getElementById('file-name');
  if(input.files[0]){
    fn.textContent = '✓ Selected: ' + input.files[0].name + ' (' + (input.files[0].size/1024/1024).toFixed(1) + ' MB)';
    fn.style.display='block';
    document.getElementById('drop-zone').style.borderColor='var(--ok)';
  }
}

function calcPrice(){
  const v = parseFloat(document.getElementById('seller-price').value)||0;
  const student = Math.ceil(v * 1.15);
  document.getElementById('student-price-display').textContent = v===0 ? 'FREE' : '₹'+student;
}

function toggleFree(cb){
  const priceInput = document.getElementById('seller-price');
  priceInput.disabled = cb.checked;
  priceInput.value = cb.checked ? 0 : '';
  document.getElementById('student-price-display').textContent = cb.checked ? 'FREE' : '';
}

// Drag and drop
const dz = document.getElementById('drop-zone');
['dragover','dragenter'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.classList.add('drag')}));
['dragleave','drop'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.classList.remove('drag')}));
dz.addEventListener('drop',ev=>{
  const files = ev.dataTransfer.files;
  if(files.length){document.getElementById('pdf-file').files=files;fileChosen(document.getElementById('pdf-file'));}
});

calcPrice();
</script>
@endpush
@endsection
