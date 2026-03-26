@extends('layouts.app')
@section('title','Reset Password — Naukaridarpan')
@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><svg width="48" height="48" viewBox="0 0 40 40" fill="none" style="display:block;margin:0 auto .5rem"><circle cx="20" cy="20" r="19" fill="#0D5C63"/><path d="M11 29L20 11L29 29" stroke="#E8650A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 23H26" stroke="#E8650A" stroke-width="2" stroke-linecap="round"/><circle cx="20" cy="11" r="2.5" fill="#D4A017"/></svg>Naukari<span>darpan</span></div>
    <h2 class="text-center mb-1" style="font-size:1.4rem">Set new password</h2>
    <p class="text-center text-muted mb-3" style="font-size:.9rem">Choose a strong password for your account</p>
    @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
    <form action="{{ route('password.update') }}" method="POST">@csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
      <div class="form-group"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="Min 8 characters" required></div>
      <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required></div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
    </form>
  </div>
</div>
@endsection
