@extends('layouts.app')
@section('title','Become a Seller — Naukaridarpan')
@section('content')
<div class="auth-wrap" style="align-items:flex-start;padding:3rem 1rem">
  <div style="width:100%;max-width:920px;margin:0 auto">
    {{-- Benefits header --}}
    <div style="background:var(--teal);border-radius:var(--r4);padding:2.5rem;display:flex;gap:3rem;align-items:center;flex-wrap:wrap;margin-bottom:2rem">
      <div>
        <h2 style="color:#fff;margin-bottom:.5rem">Start Earning from Your Knowledge</h2>
        <p style="color:rgba(255,255,255,.75)">Upload exam papers, set your price, and earn from every student purchase. Payouts within 48 hours.</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;flex-shrink:0">
        @foreach([['15%','Platform commission only'],['48hrs','Settlement to wallet'],['₹0','Zero joining fee']] as [$v,$l])
        <div style="text-align:center;color:#fff">
          <div style="font-family:var(--fd);font-size:1.6rem">{{ $v }}</div>
          <div style="font-size:.78rem;opacity:.7;font-family:var(--fu);margin-top:.2rem">{{ $l }}</div>
        </div>
        @endforeach
      </div>
    </div>

    <div class="g-grid" style="grid-template-columns:1fr 1.6fr;gap:2rem">
      {{-- Benefits list --}}
      <div class="card card-static card-body">
        <h3 class="mb-3" style="font-size:1.1rem">Why sell on Naukaridarpan?</h3>
        @foreach(['✅ Keep 85% of every sale','✅ AI converts your PDF to full exam','✅ Built-in secure exam engine','✅ Students from across India','✅ Detailed analytics dashboard','✅ KYC-verified, direct bank payout','✅ Free SEO-optimised professor profile','✅ No technical knowledge needed'] as $b)
        <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;font-size:.9rem;border-bottom:1px solid var(--border-l)">{{ $b }}</div>
        @endforeach
      </div>

      {{-- Form --}}
      <div class="card card-static card-body">
        <h3 class="mb-3" style="font-size:1.1rem">Create your seller account</h3>
        @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
        <form action="{{ route('register.seller') }}" method="POST">
          @csrf
          <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
            <div class="form-group" style="margin:0">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Prof. Ramesh Kumar" required>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Mobile Number</label>
              <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9876543210" required>
            </div>
          </div>
          <div class="form-group mt-2">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="professor@example.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Qualification / Degree</label>
            <input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}" placeholder="B.Tech, MA History, IAS (Retd.)…">
          </div>
          <div class="form-group">
            <label class="form-label">Institution / Organisation</label>
            <input type="text" name="institution" class="form-control" value="{{ old('institution') }}" placeholder="Your coaching centre or college">
          </div>
          <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
            <div class="form-group" style="margin:0">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
            </div>
          </div>
          <div class="form-group mt-2">
            <label class="form-check"><input type="checkbox" required> <span style="font-size:.82rem;color:var(--ink-m)">I agree to the <a href="#">Seller Agreement</a> and <a href="#">Terms of Service</a></span></label>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">Create Seller Account →</button>
        </form>
        <div style="text-align:center;margin-top:1rem;font-size:.85rem;color:var(--ink-l)">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
      </div>
    </div>
  </div>
</div>
@endsection
