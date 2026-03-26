@extends('layouts.app')
@section('title','Edit Post — Admin')
@section('content')
<div class="container section" style="max-width:860px">
  <div style="margin-bottom:1.5rem"><a href="{{ route('admin.blog.index') }}" style="font-size:.85rem;color:var(--ink-l)">← Blog Manager</a><h2 class="mt-1">Edit Post</h2></div>
  @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
  <form action="{{ route('admin.blog.update',$post) }}" method="POST">@csrf @method('PUT')
    <div class="g-grid" style="grid-template-columns:1fr 280px;gap:2rem;align-items:start">
      <div>
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title',$post->title) }}" required></div>
        <div class="form-group"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt',$post->excerpt) }}</textarea></div>
        <div class="form-group"><label class="form-label">Content *</label><textarea name="content" class="form-control" rows="20" required>{{ old('content',$post->content) }}</textarea></div>
      </div>
      <div style="position:sticky;top:80px">
        <div class="card card-static card-body">
          <div class="form-group"><label class="form-label">Category</label><select name="category" class="form-control">@foreach(['Sarkari Result','Admit Card','Vacancy','Exam Date','Answer Key','Study Tips','Current Affairs','Historical News','Sports News','Most Important News'] as $c)<option value="{{ $c }}" {{ $post->category==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
          <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="draft" {{ $post->status=='draft'?'selected':'' }}>Draft</option><option value="published" {{ $post->status=='published'?'selected':'' }}>Published</option><option value="archived" {{ $post->status=='archived'?'selected':'' }}>Archived</option></select></div>
          <div class="form-group"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title',$post->meta_title) }}"></div>
          <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description',$post->meta_description) }}</textarea></div>
          <div class="form-group"><label class="form-label">Featured Image URL</label><input type="url" name="featured_image" class="form-control" value="{{ old('featured_image',$post->featured_image) }}"></div>
          <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" value="{{ old('tags',implode(', ',$post->tags??[])) }}"></div>
          <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary" style="flex:1">Save</button>
            <form action="{{ route('admin.blog.destroy',$post) }}" method="POST" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Del</button></form>
          </div>
        </div>
        <div class="card card-static card-body mt-3">
          <div style="font-weight:600;font-family:var(--fu);margin-bottom:.75rem">Topic Images (Google / Pexels)</div>
          <div class="form-group">
            <label class="form-label">Search Images</label>
            <input type="text" id="img-query" class="form-control" placeholder="e.g. Sports news, Current affairs" value="{{ $post->title }}">
          </div>
          <div class="form-group">
            <label class="form-label">Source</label>
            @php $imgDefault = \App\Models\PlatformSetting::get('image_source_default','google'); @endphp
            <select id="img-source" class="form-control">
              <option value="google" {{ $imgDefault==='google'?'selected':'' }}>Google CSE</option>
              <option value="pexels" {{ $imgDefault==='pexels'?'selected':'' }}>Pexels</option>
            </select>
          </div>
          <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem">
            <button type="button" class="btn btn-primary btn-sm" id="img-search">Fetch Images</button>
            <span id="img-status" class="text-muted" style="font-size:.85rem"></span>
          </div>
          <div id="img-results" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem"></div>
          <div id="img-preview" style="margin-top:.75rem;display:{{ $post->featured_image ? 'block' : 'none' }}">
            <img id="img-preview-img" src="{{ $post->featured_image }}" alt="Selected" style="width:100%;border-radius:var(--r2)">
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@push('scripts')
<script>
const imgBtn = document.getElementById('img-search');
const imgStatus = document.getElementById('img-status');
const imgResults = document.getElementById('img-results');
imgBtn?.addEventListener('click', async () => {
  const q = document.getElementById('img-query').value.trim();
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
          const box = document.getElementById('img-preview');
          const img = document.getElementById('img-preview-img');
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
