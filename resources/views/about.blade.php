@extends('layouts.app')
@section('title','About Naukaridarpan — India\'s Exam Marketplace')
@section('content')
<div style="background:linear-gradient(135deg,var(--teal) 0%,#0A4950 100%);padding:4rem 0 3rem">
  <div class="container text-center"><h1 style="color:#fff;margin-bottom:.75rem">About Naukaridarpan</h1><p style="color:rgba(255,255,255,.75);font-size:1.1rem;max-width:560px;margin:0 auto">Connecting India's best educators with millions of competitive exam aspirants</p></div>
</div>
<div class="container" style="padding:3rem 1.25rem 5rem">
  <div style="max-width:720px;margin:0 auto">
    <h2 class="mb-3">Our Mission</h2>
    <p style="font-size:1rem;line-height:1.8;margin-bottom:2rem">Naukaridarpan was built with one goal — to make high-quality mock tests affordable and accessible to every competitive exam aspirant in India, regardless of their location or background. We connect verified educators and retired officers with students who need real exam-level practice.</p>
    <div class="g-grid grid-3 mb-4">
      @foreach([['🎯','Mission','Quality exam practice for every Indian aspirant, from Tier-1 cities to remote villages.'],['👨‍🏫','For Educators','A platform to monetise your expertise — upload papers, set prices, earn passive income.'],['🔒','Secure & Fair','Secure exam engine with anti-cheat, verified sellers, and transparent payouts.']] as [$i,$t,$d])
      <div class="card card-static card-body text-center"><div style="font-size:2rem;margin-bottom:.5rem">{{ $i }}</div><h3 style="font-size:1rem;margin-bottom:.4rem">{{ $t }}</h3><p style="font-size:.85rem">{{ $d }}</p></div>
      @endforeach
    </div>
    <h2 class="mb-3">Platform Stats</h2>
    <div class="g-grid grid-4 mb-4">
      @foreach([['500+','Educators'],['10,000+','Mock Tests'],['50,000+','Students'],['48 hrs','Payouts']] as [$v,$l])
      <div class="stat-card text-center"><div class="stat-val" style="font-size:1.6rem">{{ $v }}</div><div class="stat-label">{{ $l }}</div></div>
      @endforeach
    </div>
    <div class="cta-banner">
      <div><h2>Ready to join?</h2><p>Whether you're a student or an educator, Naukaridarpan has something for you.</p></div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="{{ route('register') }}" class="btn btn-white">Sign Up Free</a>
        <a href="{{ route('register.seller') }}" class="btn btn-lg" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff">Become a Seller</a>
      </div>
    </div>
  </div>
</div>
@endsection
