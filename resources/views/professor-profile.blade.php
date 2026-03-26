@extends('layouts.app')
@section('title', $profile->user->name.' — Educator Profile — Naukaridarpan')
@section('content')
<div style="background:linear-gradient(135deg,var(--teal) 0%,#0A4950 100%);padding:3rem 0 2.5rem">
  <div class="container">
    <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap">
      <div style="width:90px;height:90px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;flex-shrink:0;border:3px solid rgba(255,255,255,.3)">
        {{ strtoupper(substr($profile->user->name,0,2)) }}
      </div>
      <div>
        <h1 style="color:#fff;font-size:1.75rem;margin-bottom:.3rem">{{ $profile->user->name }}</h1>
        @if($profile->qualification)<div style="color:rgba(255,255,255,.75);font-size:.9rem;margin-bottom:.25rem">{{ $profile->qualification }}</div>@endif
        @if($profile->institution)<div style="color:rgba(255,255,255,.65);font-size:.85rem">{{ $profile->institution }}</div>@endif
        @if($profile->rating>0)
        <div class="stars mt-2" style="font-size:1rem">{{ str_repeat('★',round($profile->rating)) }}{{ str_repeat('☆',5-round($profile->rating)) }}
          <span style="color:rgba(255,255,255,.55);font-size:.8rem;font-family:var(--fu)"> ({{ $profile->total_reviews }} reviews)</span>
        </div>
        @endif
      </div>
      <div style="margin-left:auto;display:flex;gap:2rem;flex-wrap:wrap">
        @foreach([[number_format($profile->total_sales),'Total Sales'],[number_format($profile->examPapers()->where('status','approved')->count()),'Papers'],[$profile->is_verified?'Verified':'Unverified','Status']] as [$v,$l])
        <div style="text-align:center">
          <div style="font-family:var(--fd);font-size:1.5rem;color:#fff">{{ $v }}</div>
          <div style="font-size:.78rem;color:rgba(255,255,255,.6);font-family:var(--fu)">{{ $l }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
<div class="container" style="padding:2rem 1.25rem 4rem">
  <div style="display:grid;grid-template-columns:1fr 280px;gap:2rem;align-items:start">
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
        <h2 style="font-size:1.25rem">Exam Papers by {{ $profile->user->name }}</h2>
      </div>
      @if($exams->count())
        <div class="exam-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
          @foreach($exams as $exam)@include('components.exam-card',['exam'=>$exam])@endforeach
        </div>
        <div style="margin-top:1.5rem">{{ $exams->links() }}</div>
      @else
        <div class="card card-static card-body text-center" style="padding:3rem"><p class="text-muted">No approved papers yet.</p></div>
      @endif
    </div>
    <aside style="position:sticky;top:80px">
      <div class="card card-static card-body">
        @if($profile->bio)<div style="font-size:.9rem;line-height:1.7;color:var(--ink-m);margin-bottom:1.25rem">{{ $profile->bio }}</div>@endif
        @foreach([[$profile->city&&$profile->state,$profile->city.', '.$profile->state,'📍'],[$profile->subjects,'Subjects: '.implode(', ',$profile->subjects??[]),'📚'],[$profile->website,$profile->website,'🌐'],[$profile->linkedin,$profile->linkedin,'🔗'],[$profile->youtube_channel,$profile->youtube_channel,'▶️']] as [$show,$text,$icon])
        @if($show)<div style="display:flex;gap:.5rem;font-size:.85rem;color:var(--ink-m);padding:.4rem 0;border-bottom:1px solid var(--border-l)"><span>{{ $icon }}</span><span>{{ $text }}</span></div>@endif
        @endforeach
        <div style="margin-top:1rem;padding-top:1rem;display:flex;gap:.75rem;font-size:.82rem;font-family:var(--fu)">
          <div style="flex:1;text-align:center;background:var(--cream);border-radius:var(--r2);padding:.5rem"><div style="font-weight:700;color:var(--teal)">{{ number_format($profile->total_earnings,0) }}+</div><div style="color:var(--ink-l);font-size:.75rem">Total Earnings</div></div>
          <div style="flex:1;text-align:center;background:var(--cream);border-radius:var(--r2);padding:.5rem"><div style="font-weight:700;color:var(--teal)">{{ $profile->total_sales }}</div><div style="color:var(--ink-l);font-size:.75rem">Students</div></div>
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection
