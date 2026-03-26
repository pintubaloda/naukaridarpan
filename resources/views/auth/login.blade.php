@extends('layouts.app')
@section('title','Login — Naukaridarpan')
@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">
      <svg width="48" height="48" viewBox="0 0 40 40" fill="none" style="display:block;margin:0 auto .5rem"><circle cx="20" cy="20" r="19" fill="#0D5C63"/><path d="M11 29L20 11L29 29" stroke="#E8650A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 23H26" stroke="#E8650A" stroke-width="2" stroke-linecap="round"/><circle cx="20" cy="11" r="2.5" fill="#D4A017"/></svg>
      Naukari<span>darpan</span>
    </div>
    <h2 class="text-center mb-1" style="font-size:1.4rem">Welcome back</h2>
    <p class="text-center text-muted mb-3" style="font-size:.9rem">Sign in to continue your exam preparation</p>

    @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
      </div>
      <div class="form-group">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
          <label class="form-label" style="margin:0">Password</label>
          <a href="{{ route('password.request') }}" style="font-size:.82rem">Forgot password?</a>
        </div>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <div class="form-group">
        <label class="form-check"><input type="checkbox" name="remember"> <span style="font-size:.88rem;color:var(--ink-m)">Remember me for 30 days</span></label>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
    </form>

    <div class="auth-divider">or</div>

    <div style="text-align:center;font-size:.9rem;color:var(--ink-m)">
      Don't have an account?
      <a href="{{ route('register') }}" class="fw-600">Create free account</a>
    </div>
    <div style="text-align:center;margin-top:.75rem;font-size:.85rem;color:var(--ink-l)">
      Are you an educator? <a href="{{ route('register.seller') }}">Register as Seller</a>
    </div>
  </div>
</div>
@endsection
