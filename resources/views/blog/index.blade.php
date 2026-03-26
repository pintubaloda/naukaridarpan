@extends('layouts.app')
@section('title','Sarkari Naukri Blog — Results, Admit Cards, Vacancies — Naukaridarpan')
@section('content')
<div class="container" style="padding:2rem 1.25rem 4rem">
  {{-- Header --}}
  <div style="text-align:center;padding:2rem 0 2.5rem">
    <h1 style="margin-bottom:.5rem">Sarkari Naukri Blog</h1>
    <p class="text-muted">Latest results, admit cards, vacancies and study tips — updated daily</p>
  </div>

  {{-- Category filter pills --}}
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:2.5rem">
    <a href="{{ route('blog.index') }}" class="badge {{ !request('category') ? 'badge-saffron' : 'badge-gray' }}" style="padding:.4rem .9rem;font-size:.8rem">All Posts</a>
    @foreach($categories as $cat)
    <a href="{{ route('blog.index',['category'=>$cat]) }}" class="badge {{ request('category')==$cat ? 'badge-saffron' : 'badge-gray' }}" style="padding:.4rem .9rem;font-size:.8rem">{{ $cat }}</a>
    @endforeach
  </div>

  {{-- Featured post --}}
  @if($featured && !request('category'))
  <a href="{{ route('blog.show',$featured->slug) }}" style="text-decoration:none;display:block;margin-bottom:2.5rem">
    <div class="card card-static" style="display:grid;grid-template-columns:1fr 1.4fr;overflow:hidden">
      <div style="background:var(--teal-l);min-height:220px;display:flex;align-items:center;justify-content:center;font-size:4rem">📰</div>
      <div style="padding:2rem">
        <div class="blog-cat" style="margin-bottom:.5rem">{{ $featured->category }} · Featured</div>
        <h2 style="font-size:1.4rem;margin-bottom:.75rem;line-height:1.3">{{ $featured->title }}</h2>
        <p style="font-size:.9rem;color:var(--ink-m);line-height:1.65">{{ $featured->excerpt }}</p>
        <div style="margin-top:1rem;display:flex;align-items:center;gap:1rem;font-size:.8rem;color:var(--ink-l);font-family:var(--fu)">
          <span>{{ $featured->published_at?->format('d M Y') }}</span>
          <span>·</span>
          <span>{{ $featured->view_count }} views</span>
          @if($featured->is_ai_generated)<span class="badge badge-teal">AI Generated</span>@endif
        </div>
      </div>
    </div>
  </a>
  @endif

  {{-- Search --}}
  <div style="max-width:500px;margin:0 auto 2rem">
    <form action="{{ route('blog.index') }}" method="GET" style="position:relative">
      @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
      <svg style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--ink-l);pointer-events:none" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/></svg>
      <input type="search" name="search" class="form-control" style="padding-left:2.5rem;border-radius:999px" placeholder="Search blog posts…" value="{{ request('search') }}">
    </form>
  </div>

  {{-- Posts grid --}}
  @if($posts->count())
  <div class="g-grid grid-3">
    @foreach($posts as $post)
    <a href="{{ route('blog.show',$post->slug) }}" style="text-decoration:none">
      <div class="card blog-card" style="height:100%">
        <div class="blog-thumb" style="background:var(--teal-l);display:flex;align-items:center;justify-content:center;font-size:2rem">
          @php $icons=['Sarkari Result'=>'📋','Admit Card'=>'🪪','Vacancy'=>'💼','Exam Date'=>'📅','Answer Key'=>'🔑','Study Tips'=>'📚','Current Affairs'=>'📰']; @endphp
          {{ $icons[$post->category] ?? '📝' }}
        </div>
        <div class="blog-body">
          <div class="blog-cat">{{ $post->category }}</div>
          <div class="blog-title">{{ $post->title }}</div>
          @if($post->excerpt)<p style="font-size:.82rem;color:var(--ink-l);line-height:1.5;margin-bottom:.5rem">{{ Str::limit($post->excerpt,100) }}</p>@endif
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.76rem;color:var(--ink-l);font-family:var(--fu)">
            <span>{{ $post->published_at?->format('d M Y') }}</span>
            @if($post->is_ai_generated)<span class="badge badge-gray" style="font-size:.65rem">AI</span>@endif
          </div>
        </div>
      </div>
    </a>
    @endforeach
  </div>
  <div style="margin-top:2rem">{{ $posts->links() }}</div>
  @else
  <div class="text-center" style="padding:4rem 0;color:var(--ink-l)">
    <div style="font-size:3rem;margin-bottom:1rem">📭</div>
    <p>No posts found. <a href="{{ route('blog.index') }}">View all posts</a></p>
  </div>
  @endif
</div>
@endsection
