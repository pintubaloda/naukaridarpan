@extends('layouts.app')
@section('title','Forgot Password — Naukaridarpan')
@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><svg width="48" height="48" viewBox="0 0 40 40" fill="none" style="display:block;margin:0 auto .5rem"><circle cx="20" cy="20" r="19" fill="#0D5C63"/><path d="M11 29L20 11L29 29" stroke="#E8650A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 23H26" stroke="#E8650A" stroke-width="2" stroke-linecap="round"/><circle cx="20" cy="11" r="2.5" fill="#D4A017"/></svg>Naukari<span>darpan</span></div>
    <h2 class="text-center mb-1" style="font-size:1.4rem">Reset your password</h2>
    <p class="text-center text-muted mb-3" style="font-size:.9rem">Enter your registered email to receive a reset link</p>
    @if(session('status'))<div class="alert alert-success mb-3">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
    <form action="{{ route('password.email') }}" method="POST">@csrf
      <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required autofocus></div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
    </form>
    <div style="text-align:center;margin-top:1.25rem;font-size:.88rem"><a href="{{ route('login') }}">← Back to Login</a></div>
  </div>
</div>
@endsection
