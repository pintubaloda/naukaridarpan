@extends('layouts.app')
@section('title',"Naukaridarpan — India's #1 Mock Test Marketplace for UPSC, SSC, Banking & Railways")
@section('content')

{{-- HERO --}}
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        <svg width="12" height="12" fill="#F4833D" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        India's Most Trusted Exam Marketplace
      </div>
      <h1>Practice Smarter.<br>Crack Your Dream Exam.</h1>
      <p class="subtitle">Verified mock tests from top educators — UPSC, SSC, Banking, Railway &amp; all competitive exams.</p>
      <form action="{{ route('exams.browse') }}" method="GET" class="hero-search">
        <input type="text" name="search" placeholder="Search UPSC, SSC CGL, IBPS PO, Railway NTPC…" autocomplete="off">
        <button type="submit" class="btn btn-primary btn-lg">Search Exams</button>
      </form>
      <div class="hero-stats">
        @foreach([[$stats['total_exams'],'Mock Tests'],[$stats['total_sellers'],'Verified Educators'],[$stats['total_students'],'Students Enrolled'],[$stats['free_exams'],'Free PYQ Papers']] as [$n,$l])
        <div class="hero-stat"><span class="num">{{ number_format($n) }}+</span><span class="lbl">{{ $l }}</span></div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- CATEGORIES --}}
<section class="section" style="padding-bottom:2rem">
  <div class="container">
    <div class="section-header">
      <div><h2>Browse by Exam Category</h2><p>From Panchayat to IAS — every competitive exam covered</p></div>
      <a href="{{ route('exams.browse') }}" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="category-grid">
      @php $icons=['upsc'=>'🏛️','ssc'=>'📋','banking'=>'🏦','railway'=>'🚂','state-psc'=>'🗺️','defence'=>'🎖️','police'=>'👮','teaching'=>'📚','neet'=>'🩺','jee'=>'⚙️','gate'=>'🔬','law'=>'⚖️']; @endphp
      @foreach($categories as $cat)
      <a href="{{ route('category',$cat->slug) }}" class="category-card">
        <span class="cat-icon">{{ $icons[$cat->slug] ?? '📝' }}</span>
        <span class="cat-name">{{ $cat->name }}</span>
        <span class="cat-count">{{ $cat->exam_papers_count }} papers</span>
      </a>
      @endforeach
    </div>
  </div>
</section>

{{-- FEATURED EXAMS --}}
<section class="section" style="padding-top:2rem">
  <div class="container">
    <div class="section-header">
      <div><h2>Most Popular Mock Tests</h2><p>Trusted by thousands of aspirants across India</p></div>
      <a href="{{ route('exams.browse') }}" class="text-saffron fw-600">View All →</a>
    </div>
    <div class="exam-grid">
      @foreach($featuredExams as $exam)@include('components.exam-card',['exam'=>$exam])@endforeach
    </div>
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="section" style="background:var(--white);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <h2 class="text-center mb-4">How Naukaridarpan Works</h2>
    <div class="g-grid grid-3">
      @foreach([['🔍','Find Your Exam','Browse hundreds of mock tests by category, exam, difficulty or professor.'],['💳','Buy Securely','Pay via UPI, card or netbanking. Instant access after payment via Razorpay.'],['📊','Practice & Improve','Secure exam engine. Detailed result with answer analysis and rank.']] as [$i,$t,$d])
      <div class="card card-static card-body text-center">
        <div style="font-size:2.5rem;margin-bottom:.75rem">{{ $i }}</div>
        <h3 class="mb-2">{{ $t }}</h3><p style="font-size:.9rem">{{ $d }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- FREE EXAMS --}}
@if($freeExams->count())
<section class="section">
  <div class="container">
    <div class="section-header">
      <div><h2>Free Previous Year Papers</h2><p>Official PYQs — scraped, AI-parsed and ready to practice</p></div>
      <a href="{{ route('exams.browse',['price'=>'free']) }}" class="text-saffron fw-600">All Free Papers →</a>
    </div>
    <div class="exam-grid">@foreach($freeExams as $exam)@include('components.exam-card',['exam'=>$exam])@endforeach</div>
  </div>
</section>
@endif

{{-- TOP PROFESSORS --}}
@if($topSellers->count())
<section class="section bg-teal-l" style="border-top:1px solid #cce7ea;border-bottom:1px solid #cce7ea">
  <div class="container">
    <div class="section-header">
      <div><h2>Top Educators on Naukaridarpan</h2><p>Learn from India's best competitive exam professors</p></div>
      <a href="{{ route('exams.browse') }}" class="text-teal fw-600">Browse All →</a>
    </div>
    <div class="g-grid grid-3">
      @foreach($topSellers as $seller)
      @php $p=$seller->sellerProfile; @endphp
      @if($p)
      <a href="{{ route('professor.profile',$p->username) }}" style="text-decoration:none">
        <div class="card prof-card">
          <div class="prof-avatar">{{ strtoupper(substr($seller->name,0,2)) }}</div>
          <div class="prof-name">{{ $seller->name }}</div>
          <div class="prof-title">{{ $p->qualification ?? $p->institution ?? 'Educator' }}</div>
          @if($p->rating>0)<div class="stars mb-2" style="font-size:.9rem">{{ str_repeat('★',round($p->rating)) }}{{ str_repeat('☆',5-round($p->rating)) }} <span class="text-muted" style="font-size:.76rem">({{ $p->total_reviews }})</span></div>@endif
          <div class="prof-stats"><span>📄 {{ $p->examPapers()->where('status','approved')->count() }} papers</span><span>👥 {{ number_format($p->total_sales) }} sales</span></div>
        </div>
      </a>
      @endif
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- BLOG --}}
@if($latestPosts->count())
<section class="section">
  <div class="container">
    <div class="section-header">
      <div><h2>Sarkari Naukri Blog</h2><p>Results, admit cards, vacancies — updated daily by AI</p></div>
      <a href="{{ route('blog.index') }}" class="text-saffron fw-600">All Posts →</a>
    </div>
    <div class="g-grid grid-3">
      @foreach($latestPosts as $post)
      <a href="{{ route('blog.show',$post->slug) }}" style="text-decoration:none">
        <div class="card blog-card">
          <div class="blog-thumb" style="background:var(--teal-l)"></div>
          <div class="blog-body">
            <div class="blog-cat">{{ $post->category }}</div>
            <div class="blog-title">{{ $post->title }}</div>
            <div class="text-muted" style="font-size:.76rem;font-family:var(--fu)">{{ $post->published_at?->diffForHumans() }}</div>
          </div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- SELLER CTA --}}
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="cta-banner">
      <div>
        <h2>Are You a Professor or Coach?</h2>
        <p>Upload papers. Set your price. Earn from every purchase.<br>Join 500+ educators earning on Naukaridarpan. 48-hour payouts.</p>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="{{ route('register.seller') }}" class="btn btn-white btn-lg">Start Selling Today →</a>
        <a href="{{ route('about') }}" class="btn btn-lg" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff">Learn More</a>
      </div>
    </div>
  </div>
</section>
@endsection
