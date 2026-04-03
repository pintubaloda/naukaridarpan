@extends('layouts.app')
@section('title','Blog Manager — Admin')
@section('meta_robots','noindex,nofollow')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div><h2 class="mb-1">Blog Manager</h2><p class="text-muted">Manage AI and manual blog posts</p></div>
        <div style="display:flex;gap:.5rem">
          <button onclick="openAiModal()" class="btn btn-outline btn-sm" id="ai-btn">✨ Generate AI Post</button>
          <a href="{{ route('admin.blog.create') }}" class="btn btn-primary btn-sm">+ Write Post</a>
        </div>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <div id="ai-status" style="display:none" class="alert alert-info mb-3">
        <span id="ai-status-text">Generating AI post… please wait.</span>
        <span id="ai-spinner" style="margin-left:.5rem">⏳</span>
      </div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Source</th><th>Views</th><th>Date</th><th></th></tr></thead>
          <tbody>
            @foreach($posts as $post)
            <tr>
              <td style="font-weight:500;font-family:var(--fu);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $post->title }}</td>
              <td><span class="badge badge-gray">{{ $post->category }}</span></td>
              <td><span class="badge {{ ['draft'=>'badge-gold','published'=>'badge-green','archived'=>'badge-gray'][$post->status]??'badge-gray' }}">{{ ucfirst($post->status) }}</span></td>
              <td>@if($post->is_ai_generated)<span class="badge badge-teal">AI</span>@else<span class="badge badge-gray">Manual</span>@endif</td>
              <td>{{ number_format($post->view_count) }}</td>
              <td class="text-muted" style="white-space:nowrap">{{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}</td>
              <td>
                <div style="display:flex;gap:.4rem">
                  <a href="{{ route('admin.blog.edit',$post) }}" class="btn btn-ghost btn-sm">Edit</a>
                  <form action="{{ route('admin.blog.destroy',$post) }}" method="POST" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Del</button></form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:1.5rem">{{ $posts->links() }}</div>
    </main>
  </div>
</div>
<!-- AI Generate Modal -->
<div id="ai-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.35);display:none;align-items:center;justify-content:center;z-index:1000;padding:1rem">
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;max-width:520px;width:100%;padding:1.5rem 1.75rem;box-shadow:var(--s3)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <div style="font-weight:700;font-family:var(--fu)">Generate AI Blog</div>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeAiModal()">Close</button>
    </div>
    <div class="form-group">
      <label class="form-label">Topic</label>
      <input type="text" id="ai-topic" class="form-control" placeholder="e.g. March 2026 Current Affairs">
    </div>
    <div class="form-group">
      <label class="form-label">Category</label>
      <select id="ai-category" class="form-control">
        @foreach(['Sarkari Result','Admit Card','Vacancy','Exam Date','Answer Key','Study Tips','Current Affairs','Historical News','Sports News','Most Important News'] as $c)
          <option value="{{ $c }}">{{ $c }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Language</label>
      <select id="ai-language" class="form-control">
        <option value="English">English</option>
        <option value="Hindi">Hindi</option>
      </select>
    </div>
    <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:.75rem">
      <button type="button" class="btn btn-ghost" onclick="closeAiModal()">Cancel</button>
      <button type="button" class="btn btn-primary" id="ai-generate-btn" onclick="generateAI()">Generate</button>
    </div>
  </div>
</div>
@push('scripts')
<script>
function openAiModal(){ document.getElementById('ai-modal').style.display='flex'; }
function closeAiModal(){ document.getElementById('ai-modal').style.display='none'; }
async function generateAI(){
  document.getElementById('ai-status').style.display='flex';
  document.getElementById('ai-btn').disabled=true;
  const genBtn = document.getElementById('ai-generate-btn');
  if (genBtn) genBtn.disabled = true;
  try{
    const topic = (document.getElementById('ai-topic')?.value || '').trim();
    const category = (document.getElementById('ai-category')?.value || 'Current Affairs');
    const language = (document.getElementById('ai-language')?.value || 'English');
    if (!topic) { document.getElementById('ai-status').style.display='none'; if (genBtn) genBtn.disabled=false; return; }
    closeAiModal();
    const r=await fetch('{{ route('admin.blog.generate') }}',{
      method:'POST',
      headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}',
        'Content-Type':'application/json',
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      },
      credentials:'same-origin',
      body:JSON.stringify({language, topic, category})
    });
    const ct = r.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const text = await r.text();
      throw new Error('Unexpected response. Please login again. ' + text.slice(0,120));
    }
    const d=await r.json();
    if(d.success){
      document.getElementById('ai-status-text').textContent='✓ Draft saved successfully.';
      document.getElementById('ai-spinner').style.display='none';
      setTimeout(() => {
        window.location.href = d.edit_url || '{{ route('admin.blog.index') }}';
      }, 400);
    } else {
      document.getElementById('ai-status').className='alert alert-error mb-3';
      document.getElementById('ai-status-text').textContent='Generation failed: '+d.message;
      document.getElementById('ai-spinner').style.display='none';
    }
  }catch(e){document.getElementById('ai-status').className='alert alert-error mb-3';document.getElementById('ai-status').textContent='Error: '+e.message;}
  document.getElementById('ai-btn').disabled=false;
  if (genBtn) genBtn.disabled=false;
}
</script>
@endpush
@endsection
