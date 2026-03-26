@extends('layouts.app')
@section('title', $post->meta_title ?: $post->title.' — Naukaridarpan Blog')
@section('meta_desc', $post->meta_description ?: $post->excerpt)
@section('content')
<div class="container" style="padding:2rem 1.25rem 4rem">
  <div style="display:grid;grid-template-columns:1fr 300px;gap:3rem;align-items:start;max-width:1100px;margin:0 auto">
    <article>
      <div style="margin-bottom:.75rem">
        <a href="{{ route('blog.index') }}" style="font-size:.82rem;color:var(--ink-l)">← Blog</a>
        <span style="color:var(--ink-l);font-size:.82rem"> / {{ $post->category }}</span>
      </div>
      <span class="badge badge-saffron mb-2">{{ $post->category }}</span>
      <h1 style="font-size:2rem;line-height:1.25;margin-bottom:1rem">{{ $post->title }}</h1>
      <div style="display:flex;align-items:center;gap:1rem;font-size:.82rem;color:var(--ink-l);font-family:var(--fu);margin-bottom:2rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border)">
        <span>{{ $post->published_at?->format('d M Y, g:i A') }}</span>
        <span>·</span><span>{{ $post->view_count }} views</span>
        @if($post->is_ai_generated)<span class="badge badge-teal">AI Generated</span>@endif
      </div>
      @if($post->excerpt)
      <div style="background:var(--teal-l);border-left:4px solid var(--teal);padding:1rem 1.25rem;border-radius:0 var(--r2) var(--r2) 0;margin-bottom:1.5rem;font-size:.95rem;color:var(--ink-m);line-height:1.65">
        {{ $post->excerpt }}
      </div>
      @endif
      <div class="blog-content" style="line-height:1.8;font-size:1rem;color:var(--ink-m)">
        {!! $post->content !!}
      </div>
      {{-- Tags --}}
      @if($post->tags)
      <div style="margin-top:2rem;padding-top:1.25rem;border-top:1px solid var(--border-l);display:flex;flex-wrap:wrap;gap:.4rem">
        @foreach($post->tags as $tag)<span class="badge badge-gray">{{ $tag }}</span>@endforeach
      </div>
      @endif
      {{-- CTA --}}
      <div style="margin-top:2.5rem;background:var(--saffron-l);border:1px solid rgba(232,101,10,.2);border-radius:var(--r3);padding:1.5rem;text-align:center">
        <h3 style="font-size:1.1rem;margin-bottom:.5rem">Practice Makes Perfect</h3>
        <p style="font-size:.9rem;color:var(--ink-m);margin-bottom:1rem">Take a free mock test and boost your preparation for {{ $post->category }}.</p>
        <a href="{{ route('exams.browse') }}" class="btn btn-primary">Browse Mock Tests →</a>
      </div>
    </article>
    {{-- Sidebar --}}
    <aside style="position:sticky;top:80px">
      @if($related->count())
      <div class="card card-static">
        <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-l);font-size:.88rem;font-weight:600;font-family:var(--fu)">Related Posts</div>
        @foreach($related as $r)
        <a href="{{ route('blog.show',$r->slug) }}" style="text-decoration:none;display:block;padding:.85rem 1rem;border-bottom:1px solid var(--border-l);transition:background .15s" onmouseover="this.style.background='var(--saffron-l)'" onmouseout="this.style.background=''">
          <div class="blog-cat" style="margin-bottom:.25rem">{{ $r->category }}</div>
          <div style="font-size:.85rem;font-weight:600;color:var(--ink);line-height:1.35;font-family:var(--fu)">{{ Str::limit($r->title,70) }}</div>
          <div style="font-size:.76rem;color:var(--ink-l);margin-top:.3rem;font-family:var(--fu)">{{ $r->published_at?->format('d M Y') }}</div>
        </a>
        @endforeach
      </div>
      @endif
      <div class="card card-static mt-3" style="background:var(--teal);border-color:var(--teal);padding:1.25rem;text-align:center">
        <div style="font-size:1.8rem;margin-bottom:.5rem">📝</div>
        <h3 style="color:#fff;font-size:1rem;margin-bottom:.4rem">Free Mock Tests</h3>
        <p style="color:rgba(255,255,255,.75);font-size:.82rem;margin-bottom:1rem">Practice PYQ papers from UPSC, SSC, Railway &amp; more.</p>
        <a href="{{ route('exams.browse',['price'=>'free']) }}" class="btn btn-white btn-sm w-full" style="justify-content:center">Start Free →</a>
      </div>
    </aside>
  </div>
</div>
<style>
.blog-content h2{font-family:var(--fd);font-size:1.35rem;color:var(--ink);margin:2rem 0 .75rem;border-bottom:2px solid var(--saffron-l);padding-bottom:.4rem}
.blog-content h3{font-family:var(--fd);font-size:1.1rem;color:var(--ink);margin:1.5rem 0 .5rem}
.blog-content p{margin-bottom:1rem;color:var(--ink-m);line-height:1.8}
.blog-content ul,.blog-content ol{margin:0 0 1rem 1.5rem;color:var(--ink-m)}
.blog-content li{margin-bottom:.4rem;line-height:1.65}
.blog-content table{width:100%;border-collapse:collapse;margin:1.25rem 0;font-size:.9rem}
.blog-content th{background:var(--teal);color:#fff;padding:.6rem .9rem;text-align:left;font-family:var(--fu);font-size:.82rem}
.blog-content td{padding:.6rem .9rem;border-bottom:1px solid var(--border-l)}
.blog-content tr:hover td{background:var(--saffron-l)}
.blog-content strong{color:var(--ink);font-weight:600}
</style>
@endsection
