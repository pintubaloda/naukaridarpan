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
          <div class="form-group"><label class="form-label">Category</label><select name="category" class="form-control">@foreach(['Sarkari Result','Admit Card','Vacancy','Exam Date','Answer Key','Study Tips','Current Affairs'] as $c)<option value="{{ $c }}" {{ $post->category==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
          <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="draft" {{ $post->status=='draft'?'selected':'' }}>Draft</option><option value="published" {{ $post->status=='published'?'selected':'' }}>Published</option><option value="archived" {{ $post->status=='archived'?'selected':'' }}>Archived</option></select></div>
          <div class="form-group"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title',$post->meta_title) }}"></div>
          <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description',$post->meta_description) }}</textarea></div>
          <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" value="{{ old('tags',implode(', ',$post->tags??[])) }}"></div>
          <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary" style="flex:1">Save</button>
            <form action="{{ route('admin.blog.destroy',$post) }}" method="POST" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Del</button></form>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
