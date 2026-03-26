@extends('layouts.app')
@section('title','My Profile — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.student-sidebar')
    <main>
      <h2 class="mb-4">My Profile</h2>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <div class="card card-static" style="max-width:540px">
        <div class="card-body">
          <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border-l)">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--teal);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;flex-shrink:0">{{ strtoupper(substr($user->name,0,2)) }}</div>
            <div>
              <div style="font-weight:600;font-size:1rem;font-family:var(--fu)">{{ $user->name }}</div>
              <div style="font-size:.82rem;color:var(--ink-l)">{{ $user->email }}</div>
              <div style="margin-top:.3rem"><span class="badge badge-teal">Student</span></div>
            </div>
          </div>
          <form action="{{ route('student.profile.update') }}" method="POST">@csrf @method('PUT')
            <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="{{ $user->name }}" required></div>
            <div class="form-group"><label class="form-label">Mobile Number</label><input type="tel" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="+91 98765 43210"></div>
            <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-control" value="{{ $user->email }}" disabled style="opacity:.6;cursor:not-allowed"><div class="form-hint">Email cannot be changed</div></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
