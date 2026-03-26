@extends('layouts.app')
@section('title','Create Account — Naukaridarpan')
@section('content')
<div class="auth-wrap">
  <div class="auth-card" style="max-width:480px">
    <div class="auth-logo"><svg width="48" height="48" viewBox="0 0 40 40" fill="none" style="display:block;margin:0 auto .5rem"><circle cx="20" cy="20" r="19" fill="#0D5C63"/><path d="M11 29L20 11L29 29" stroke="#E8650A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 23H26" stroke="#E8650A" stroke-width="2" stroke-linecap="round"/><circle cx="20" cy="11" r="2.5" fill="#D4A017"/></svg>Naukari<span>darpan</span></div>
    <h2 class="text-center mb-1" style="font-size:1.4rem">Create your free account</h2>
    <p class="text-center text-muted mb-3" style="font-size:.9rem">Join 50,000+ aspirants practicing on Naukaridarpan</p>

    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

    <form action="{{ route('register') }}" method="POST">
      @csrf
      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
        <div class="form-group" style="margin:0">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Priya Sharma" required>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Mobile Number</label>
          <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9876543210">
        </div>
      </div>
      <div class="form-group mt-2">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required>
      </div>
      <div class="g-grid mt-1" style="grid-template-columns:1fr 1fr;gap:.75rem">
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
        <label class="form-check">
          <input type="checkbox" required>
          <span style="font-size:.82rem;color:var(--ink-m)">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">Create Free Account</button>
    </form>

    <div class="auth-divider">or</div>
    <div style="text-align:center;font-size:.9rem;color:var(--ink-m)">Already have an account? <a href="{{ route('login') }}" class="fw-600">Sign in</a></div>
    <div style="text-align:center;margin-top:.75rem;font-size:.85rem;color:var(--ink-l)">Are you an educator? <a href="{{ route('register.seller') }}">Register as Seller</a></div>
  </div>
</div>
@endsection
