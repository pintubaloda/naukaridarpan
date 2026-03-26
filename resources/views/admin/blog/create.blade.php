@extends('layouts.app')
@section('title','Create Blog Post — Admin')
@section('meta_robots','noindex,nofollow')
@section('content')
<div class="container section" style="max-width:860px">
  <div style="margin-bottom:1.5rem"><a href="{{ route('admin.blog.index') }}" style="font-size:.85rem;color:var(--ink-l)">← Blog Manager</a><h2 class="mt-1">Create New Post</h2></div>
  @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
  <form action="{{ route('admin.blog.store') }}" method="POST" id="blog-form">@csrf
    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">AI Draft Generator</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:2fr 1fr 1fr;gap:1rem;align-items:end">
          <div class="form-group" style="margin:0">
            <label class="form-label">Topic</label>
            <input type="text" id="ai-topic" class="form-control" placeholder="e.g. SSC CGL 2025 Notification" list="ai-topic-list">
            <datalist id="ai-topic-list">
              @php
                $suggestions = [];
                $topicJson = \App\Models\PlatformSetting::get('blog_topics_json','');
                $topicMap = $topicJson ? json_decode($topicJson, true) : [];
                if (is_array($topicMap)) {
                  foreach ($topicMap as $items) {
                    if (is_array($items)) $suggestions = array_merge($suggestions, $items);
                  }
                }
              @endphp
              @foreach(array_unique($suggestions) as $s)
                <option value="{{ $s }}"></option>
              @endforeach
            </datalist>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Category</label>
            <select id="ai-category" class="form-control">
              <option>Sarkari Result</option><option>Admit Card</option><option>Vacancy</option><option>Exam Date</option><option>Answer Key</option><option>Study Tips</option><option>Current Affairs</option><option>Historical News</option><option>Sports News</option><option>Most Important News</option>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Language</label>
            <select id="ai-language" class="form-control"><option>English</option><option>Hindi</option></select>
          </div>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center;margin-top:1rem">
          <button type="button" class="btn btn-teal" id="ai-generate">Generate Draft</button>
          <span id="ai-status" class="text-muted" style="font-size:.85rem"></span>
        </div>
        <div class="card card-static mt-3">
          <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Topic Images (Google / Pexels)</div>
          <div class="card-body">
            <div class="g-grid" style="grid-template-columns:2fr 1fr;gap:1rem;align-items:end">
              <div class="form-group" style="margin:0">
                <label class="form-label">Search Images</label>
                <input type="text" id="img-query" class="form-control" placeholder="e.g. Sports news, Current affairs">
              </div>
              <div class="form-group" style="margin:0">
                <label class="form-label">Source</label>
                @php $imgDefault = \App\Models\PlatformSetting::get('image_source_default','google'); @endphp
                <select id="img-source" class="form-control">
                  <option value="google" {{ $imgDefault==='google'?'selected':'' }}>Google CSE</option>
                  <option value="pexels" {{ $imgDefault==='pexels'?'selected':'' }}>Pexels</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:.75rem;align-items:center;margin-top:1rem">
              <button type="button" class="btn btn-primary" id="img-search">Fetch Images</button>
              <span id="img-status" class="text-muted" style="font-size:.85rem"></span>
            </div>
            <div id="img-results" style="display:grid;grid-template-columns:repeat(5,1fr);gap:.5rem;margin-top:1rem"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="g-grid" style="grid-template-columns:1fr 280px;gap:2rem;align-items:start">
      <div>
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title') }}" required></div>
        <div class="form-group"><label class="form-label">Excerpt <span class="form-hint" style="display:inline;margin:0">(shown in listing)</span></label><textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt') }}</textarea></div>
        <div class="form-group"><label class="form-label">Content * <span class="form-hint" style="display:inline;margin:0">(HTML supported)</span></label><textarea name="content" class="form-control" rows="20" required>{{ old('content') }}</textarea></div>
      </div>
      <div style="position:sticky;top:80px">
        <div class="card card-static card-body">
          <div class="form-group"><label class="form-label">Category</label><select name="category" class="form-control"><option>Sarkari Result</option><option>Admit Card</option><option>Vacancy</option><option>Exam Date</option><option>Answer Key</option><option>Study Tips</option><option>Current Affairs</option><option>Historical News</option><option>Sports News</option><option>Most Important News</option></select></div>
          <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="draft">Draft</option><option value="published">Published</option></select></div>
          <div class="form-group"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}" placeholder="60 chars max"></div>
          <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2" placeholder="160 chars max">{{ old('meta_description') }}</textarea></div>
          <div class="form-group">
            <label class="form-label">Featured Image URL</label>
            <input type="url" name="featured_image" class="form-control" value="{{ old('featured_image') }}" placeholder="https://.../image.jpg">
            <div class="form-hint">You can pick an image below or paste a URL.</div>
            <div id="ai-image-preview" style="margin-top:.5rem;display:none">
              <img id="ai-image-preview-img" src="" alt="AI placeholder" style="width:100%;border-radius:var(--r2)">
            </div>
          </div>
          <div class="form-group"><label class="form-label">Tags <span class="form-hint" style="display:inline;margin:0">(comma-separated)</span></label><input type="text" name="tags" class="form-control" value="{{ old('tags') }}"></div>
          <button type="submit" class="btn btn-primary btn-block">Publish Post</button>
        </div>
      </div>
    </div>
  </form>
</div>
@push('scripts')
<script>
const btn = document.getElementById('ai-generate');
const statusEl = document.getElementById('ai-status');
btn?.addEventListener('click', async () => {
  const topic = document.getElementById('ai-topic').value.trim();
  const category = document.getElementById('ai-category').value;
  const language = document.getElementById('ai-language').value;
  if (!topic) { statusEl.textContent = 'Please enter a topic.'; return; }
  statusEl.textContent = 'Generating...';
  btn.disabled = true;
  try {
    const res = await fetch('{{ route('admin.blog.generate') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ topic, category, language })
    });
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const text = await res.text();
      throw new Error('Unexpected response. Please login again. ' + text.slice(0,120));
    }
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed');
    const d = data.data;
    document.querySelector('input[name="title"]').value = d.title || '';
    document.querySelector('textarea[name="excerpt"]').value = d.excerpt || '';
    document.querySelector('textarea[name="content"]').value = d.body || '';
    document.querySelector('input[name="meta_title"]').value = d.meta_title || d.title || '';
    document.querySelector('textarea[name="meta_description"]').value = d.meta_description || '';
    document.querySelector('input[name="tags"]').value = (d.tags || []).join(', ');
    document.querySelector('select[name="category"]').value = d.category || category;
    statusEl.textContent = 'Draft generated. Review and publish.';
  } catch (e) {
    statusEl.textContent = 'Generation failed. Check AI settings.';
  } finally {
    btn.disabled = false;
  }
});

const imgBtn = document.getElementById('img-search');
const imgStatus = document.getElementById('img-status');
const imgResults = document.getElementById('img-results');
imgBtn?.addEventListener('click', async () => {
  const q = document.getElementById('img-query').value.trim() || document.getElementById('ai-topic').value.trim();
  const source = document.getElementById('img-source').value;
  if (!q) { imgStatus.textContent = 'Enter a topic or search term.'; return; }
  imgStatus.textContent = 'Searching...';
  imgBtn.disabled = true;
  imgResults.innerHTML = '';
  try {
    const res = await fetch(`{{ route('admin.blog.images.search') }}?query=${encodeURIComponent(q)}&source=${source}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const text = await res.text();
      throw new Error('Unexpected response. Please login again. ' + text.slice(0,120));
    }
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed');
    (data.items || []).forEach(item => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.style.border = '1px solid var(--border)';
      btn.style.borderRadius = '8px';
      btn.style.overflow = 'hidden';
      btn.style.padding = '0';
      btn.style.background = '#fff';
      btn.innerHTML = `<img src="${item.thumb}" alt="" style="width:100%;height:90px;object-fit:cover">`;
      btn.onclick = async () => {
        imgStatus.textContent = 'Saving image...';
        const r = await fetch('{{ route('admin.blog.images.attach') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ image_url: item.url })
        });
        const ct2 = r.headers.get('content-type') || '';
        if (!ct2.includes('application/json')) {
          const text = await r.text();
          throw new Error('Unexpected response. Please login again. ' + text.slice(0,120));
        }
        const d = await r.json();
        if (d.success) {
          const input = document.querySelector('input[name="featured_image"]');
          input.value = d.url;
          const box = document.getElementById('ai-image-preview');
          const img = document.getElementById('ai-image-preview-img');
          if (box && img) { img.src = d.url; box.style.display = 'block'; }
          imgStatus.textContent = 'Image saved.';
        } else {
          imgStatus.textContent = d.message || 'Failed to save image.';
        }
      };
      imgResults.appendChild(btn);
    });
    if ((data.items || []).length === 0) imgStatus.textContent = 'No images found.';
    else imgStatus.textContent = 'Select an image to attach.';
  } catch (e) {
    imgStatus.textContent = 'Image search failed. Check API keys.';
  } finally {
    imgBtn.disabled = false;
  }
});
</script>
@endpush
@endsection
