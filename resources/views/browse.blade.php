@extends('layouts.app')
@section('title','Browse Mock Tests — Naukaridarpan')
@section('content')
<div class="container" style="padding-top:2rem;padding-bottom:4rem">
  <div style="display:grid;grid-template-columns:240px 1fr;gap:2rem;align-items:start">
    <aside>
      <div class="card card-static" style="position:sticky;top:80px">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu);font-size:.9rem">Filter Exams</div>
        <form action="{{ route('exams.browse') }}" method="GET" style="padding:1rem 1.25rem">
          @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category" class="form-control" onchange="this.form.submit()">
              <option value="">All Categories</option>
              @foreach($categories as $cat)<option value="{{ $cat->slug }}" {{ request('category')==$cat->slug?'selected':'' }}>{{ $cat->name }} ({{ $cat->exam_papers_count }})</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Price</label>
            <select name="price" class="form-control" onchange="this.form.submit()">
              <option value="">All</option>
              <option value="free" {{ request('price')=='free'?'selected':'' }}>Free Only</option>
              <option value="paid" {{ request('price')=='paid'?'selected':'' }}>Paid Only</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Difficulty</label>
            <select name="difficulty" class="form-control" onchange="this.form.submit()">
              <option value="">Any</option>
              <option value="easy" {{ request('difficulty')=='easy'?'selected':'' }}>Easy</option>
              <option value="medium" {{ request('difficulty')=='medium'?'selected':'' }}>Medium</option>
              <option value="hard" {{ request('difficulty')=='hard'?'selected':'' }}>Hard</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Language</label>
            <select name="language" class="form-control" onchange="this.form.submit()">
              <option value="">Any</option>
              <option value="English" {{ request('language')=='English'?'selected':'' }}>English</option>
              <option value="Hindi" {{ request('language')=='Hindi'?'selected':'' }}>Hindi</option>
              <option value="Both" {{ request('language')=='Both'?'selected':'' }}>Bilingual</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Sort By</label>
            <select name="sort" class="form-control" onchange="this.form.submit()">
              <option value="popular" {{ request('sort','popular')=='popular'?'selected':'' }}>Most Popular</option>
              <option value="newest" {{ request('sort')=='newest'?'selected':'' }}>Newest First</option>
              <option value="price_asc" {{ request('sort')=='price_asc'?'selected':'' }}>Price Low→High</option>
              <option value="price_desc" {{ request('sort')=='price_desc'?'selected':'' }}>Price High→Low</option>
            </select>
          </div>
          @if(request()->hasAny(['category','price','difficulty','language','sort']))<a href="{{ route('exams.browse') }}" class="btn btn-ghost btn-sm w-full" style="justify-content:center">Clear Filters</a>@endif
        </form>
      </div>
    </aside>
    <main>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem">
        <div>
          @if(request('search'))<h2 style="font-size:1.3rem">Results for "<span class="text-saffron">{{ request('search') }}</span>"</h2>
          @else<h2 style="font-size:1.3rem">All Mock Tests</h2>@endif
          <p class="text-muted" style="font-size:.85rem;margin-top:.2rem">{{ $exams->total() }} papers found</p>
        </div>
      </div>
      @if($exams->count())
        <div class="exam-grid">@foreach($exams as $exam)@include('components.exam-card',['exam'=>$exam])@endforeach</div>
        <div style="margin-top:2rem">{{ $exams->links() }}</div>
      @else
        <div class="card card-static card-body text-center" style="padding:4rem 2rem">
          <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
          <h3>No exams found</h3>
          <p class="mt-2">Try adjusting your filters or <a href="{{ route('exams.browse') }}">browse all exams</a>.</p>
        </div>
      @endif
    </main>
  </div>
</div>
@endsection
