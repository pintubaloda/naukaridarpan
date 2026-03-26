@extends('layouts.app')
@section('title','Blog Manager — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div><h2 class="mb-1">Blog Manager</h2><p class="text-muted">Manage AI and manual blog posts</p></div>
        <div style="display:flex;gap:.5rem">
          <button onclick="generateAI()" class="btn btn-outline btn-sm" id="ai-btn">✨ Generate AI Post</button>
          <a href="{{ route('admin.blog.create') }}" class="btn btn-primary btn-sm">+ Write Post</a>
        </div>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <div id="ai-status" style="display:none" class="alert alert-info mb-3">Generating AI post… please wait.</div>
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
@push('scripts')
<script>
async function generateAI(){
  document.getElementById('ai-status').style.display='flex';
  document.getElementById('ai-btn').disabled=true;
  try{
    const r=await fetch('{{ route('admin.blog.generate') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},body:JSON.stringify({language:'English'})});
    const d=await r.json();
    if(d.success){document.getElementById('ai-status').textContent='✓ Post created: '+d.title+' (saved as draft)';setTimeout(()=>location.reload(),2000);}
    else{document.getElementById('ai-status').className='alert alert-error mb-3';document.getElementById('ai-status').textContent='Generation failed: '+d.message;}
  }catch(e){document.getElementById('ai-status').className='alert alert-error mb-3';document.getElementById('ai-status').textContent='Error: '+e.message;}
  document.getElementById('ai-btn').disabled=false;
}
</script>
@endpush
@endsection
