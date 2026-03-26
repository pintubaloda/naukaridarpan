@extends('layouts.app')
@section('title','Create Blog Post — Admin')
@section('content')
<div class="container section" style="max-width:860px">
  <div style="margin-bottom:1.5rem"><a href="{{ route('admin.blog.index') }}" style="font-size:.85rem;color:var(--ink-l)">← Blog Manager</a><h2 class="mt-1">Create New Post</h2></div>
  @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
  <form action="{{ route('admin.blog.store') }}" method="POST">@csrf
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
          <div class="form-group"><label class="form-label">Featured Image URL</label><input type="url" name="featured_image" class="form-control" value="{{ old('featured_image') }}" placeholder="https://.../image.jpg"></div>
          <div class="form-group"><label class="form-label">Tags <span class="form-hint" style="display:inline;margin:0">(comma-separated)</span></label><input type="text" name="tags" class="form-control" value="{{ old('tags') }}"></div>
          <button type="submit" class="btn btn-primary btn-block">Publish Post</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
