@extends('layouts.app')
@section('title', $exam->title . ' — Naukaridarpan')
@section('meta_desc', Str::limit($exam->description, 155))
@section('content')
<div class="container" style="padding-top:2rem;padding-bottom:3rem">
  <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">

    {{-- LEFT CONTENT --}}
    <div>
      {{-- Breadcrumb --}}
      <div style="font-size:.82rem;color:var(--ink-l);font-family:var(--fu);margin-bottom:1rem">
        <a href="{{ route('home') }}">Home</a> / <a href="{{ route('category',$exam->category->slug) }}">{{ $exam->category->name }}</a> / <span>{{ Str::limit($exam->title,50) }}</span>
      </div>

      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap">
        <span class="badge badge-teal">{{ $exam->category->name }}</span>
        @if($exam->exam_type)
          <span class="badge badge-gray">{{ $exam->exam_type==='previous_year' ? 'PYQ' : 'Mock' }}</span>
        @endif
        <span class="badge badge-gray">{{ ucfirst($exam->difficulty) }}</span>
        <span class="badge badge-gray">{{ $exam->language }}</span>
        @if($exam->is_free)<span class="badge badge-green">Free</span>@endif
      </div>

      <h1 style="font-size:1.75rem;margin-bottom:.4rem">{{ $exam->title }}</h1>
      @if($exam->subject)
        <div style="font-size:.95rem;color:var(--ink-l);margin-bottom:.75rem">{{ $exam->subject }}</div>
      @endif

      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <div class="exam-seller">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem">{{ strtoupper(substr($exam->seller->name,0,2)) }}</div>
          <div>
            <div style="font-size:.88rem;color:var(--ink);font-weight:600;font-family:var(--fu)">{{ $exam->seller->name }}</div>
            @if($exam->seller->sellerProfile?->institution)
            <div style="font-size:.76rem;color:var(--ink-l)">{{ $exam->seller->sellerProfile->institution }}</div>
            @endif
          </div>
        </div>
        @if($exam->seller->sellerProfile)
        <a href="{{ route('professor.profile',$exam->seller->sellerProfile->username) }}" class="btn btn-ghost btn-sm">View Profile</a>
        @endif
      </div>

      {{-- Stats row --}}
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;padding:1rem;background:var(--cream);border-radius:var(--r2);margin-bottom:1.5rem">
        @foreach([['📝',$exam->total_questions.' Questions'],['⏱',$exam->duration_minutes.' Minutes'],['🏆',$exam->max_marks.' Marks'],['🔄',$exam->max_retakes.' Retakes'],['👥',number_format($exam->total_purchases).' Purchases']] as [$icon,$val])
        <div style="text-align:center">
          <div style="font-size:1.3rem">{{ $icon }}</div>
          <div style="font-size:.8rem;font-family:var(--fu);color:var(--ink-m);margin-top:.1rem">{{ $val }}</div>
        </div>
        @endforeach
      </div>

      {{-- Description --}}
      @if($exam->description)
      <div class="card card-static card-body mb-3">
        <h3 class="mb-2" style="font-size:1rem">About This Exam</h3>
        <p style="font-size:.92rem;line-height:1.7">{{ $exam->description }}</p>
      </div>
      @endif

      {{-- Question types --}}
      @if($exam->question_types)
      <div class="card card-static card-body mb-3">
        <h3 class="mb-2" style="font-size:1rem">Question Types</h3>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
          @foreach($exam->question_types as $type => $count)
          <span class="badge badge-teal">{{ strtoupper($type) }}: {{ $count }}</span>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Tags --}}
      @if($exam->tags)
      <div style="display:flex;gap:.4rem;flex-wrap:wrap">
        @foreach($exam->tags as $tag)
        <span class="badge badge-gray">{{ $tag }}</span>
        @endforeach
      </div>
      @endif
    </div>

    {{-- RIGHT SIDEBAR (purchase card) --}}
    <div style="position:sticky;top:80px">
      <div class="card card-static">
        <div style="background:var(--teal-l);padding:1.5rem;text-align:center;border-bottom:1px solid var(--border)">
          <div style="font-family:var(--fd);font-size:3rem;color:var(--teal);margin-bottom:.25rem">
            @if($exam->is_free)
              <span style="color:var(--ok)">FREE</span>
            @else
              ₹{{ number_format($exam->student_price,0) }}
            @endif
          </div>
          @if(!$exam->is_free && $exam->platform_markup > 0)
          <div style="font-size:.8rem;color:var(--ink-l);font-family:var(--fu)">Includes platform fee</div>
          @endif
          @if($exam->negative_marking > 0)
          <div class="badge badge-red mt-1" style="display:inline-flex">Negative Marking: -{{ $exam->negative_marking }}</div>
          @endif
        </div>
        <div class="card-body">
          @if($purchased)
            {{-- Already purchased --}}
            @php $purchase = $exam->purchases()->where('student_id',auth()->id())->where('payment_status','paid')->latest()->first(); @endphp
            <div class="alert alert-success mb-3">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"/></svg>
              You own this exam
            </div>
            @if($purchase && $purchase->canAttempt())
              <a href="{{ route('student.exam.start',$purchase) }}" class="btn btn-primary btn-block btn-lg mb-2">Start Exam →</a>
              <p class="text-muted text-center" style="font-size:.82rem">{{ $purchase->retakes_allowed - $purchase->retakes_used }} retake(s) remaining</p>
            @else
              <div class="alert alert-warning">No retakes remaining for this purchase.</div>
            @endif
          @elseif($exam->is_free)
            <form action="{{ route('student.checkout',$exam) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-success btn-block btn-lg">Get Free Access →</button>
            </form>
          @elseif(auth()->check())
            <form action="{{ route('student.checkout',$exam) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-primary btn-block btn-lg">Buy Now — ₹{{ number_format($exam->student_price,0) }}</button>
            </form>
            <p class="text-muted text-center mt-2" style="font-size:.8rem">Secured by Razorpay · UPI / Card / Netbanking</p>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg">Login to Purchase</a>
            <p class="text-muted text-center mt-2" style="font-size:.8rem"><a href="{{ route('register') }}">Create free account</a></p>
          @endif

          <div style="border-top:1px solid var(--border-l);margin-top:1.25rem;padding-top:1rem">
            @foreach(['Secure exam engine','Instant result & analysis','Hindi & English support','Access on any device','Valid for '.$exam->max_retakes.' attempt(s)'] as $f)
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;padding:.3rem 0;color:var(--ink-m);font-family:var(--fu)">
              <svg width="14" height="14" fill="none" stroke="var(--ok)" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2.5" stroke-linecap="round"/></svg>
              {{ $f }}
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Seller card --}}
      @if($exam->seller->sellerProfile)
      @php $p=$exam->seller->sellerProfile; @endphp
      <div class="card card-static mt-2">
        <div class="card-body">
          <h3 class="mb-2" style="font-size:.95rem">About the Educator</h3>
          <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">{{ strtoupper(substr($exam->seller->name,0,2)) }}</div>
            <div>
              <div style="font-weight:600;font-size:.9rem;font-family:var(--fu)">{{ $exam->seller->name }}</div>
              @if($p->qualification)<div style="font-size:.78rem;color:var(--ink-l)">{{ $p->qualification }}</div>@endif
            </div>
          </div>
          @if($p->rating>0)<div class="stars mb-2" style="font-size:.9rem">{{ str_repeat('★',round($p->rating)) }}{{ str_repeat('☆',5-round($p->rating)) }} <span class="text-muted" style="font-size:.76rem">({{ $p->total_reviews }} reviews)</span></div>@endif
          <div style="font-size:.82rem;color:var(--ink-l);line-height:1.5">{{ Str::limit($p->bio,120) }}</div>
          <a href="{{ route('professor.profile',$p->username) }}" class="btn btn-ghost btn-sm w-full mt-2" style="justify-content:center">View Full Profile</a>
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Related exams --}}
  @if($relatedExams->count())
  <div class="mt-4">
    <h2 class="mb-3">More {{ $exam->category->name }} Papers</h2>
    <div class="exam-grid">@foreach($relatedExams as $related)@include('components.exam-card',['exam'=>$related])@endforeach</div>
  </div>
  @endif
</div>
@endsection
