@extends('layouts.app')
@section('title','My Profile — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <h2 class="mb-1">Seller Profile</h2>
      <p class="text-muted mb-4">Your public profile — visible to all students browsing exams</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif
      <div style="display:grid;grid-template-columns:1fr 240px;gap:2rem;align-items:start">
        <form action="{{ route('seller.profile.update') }}" method="POST">@csrf @method('PUT')
          <div class="card card-static mb-3">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Info</div>
            <div class="card-body">
              <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="{{ old('name',auth()->user()->name) }}" required></div>
              <div class="form-group"><label class="form-label">Bio / About You</label><textarea name="bio" class="form-control" rows="4" placeholder="Tell students about your teaching experience, qualifications and expertise…">{{ old('bio',$profile?->bio) }}</textarea></div>
              <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
                <div class="form-group" style="margin:0"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control" value="{{ old('qualification',$profile?->qualification) }}" placeholder="B.Tech, MA, IAS (Retd.)…"></div>
                <div class="form-group" style="margin:0"><label class="form-label">Institution</label><input type="text" name="institution" class="form-control" value="{{ old('institution',$profile?->institution) }}" placeholder="College / Coaching Centre"></div>
              </div>
              <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
                <div class="form-group" style="margin:0"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city',$profile?->city) }}" placeholder="New Delhi"></div>
                <div class="form-group" style="margin:0"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="{{ old('state',$profile?->state) }}" placeholder="Delhi"></div>
              </div>
            </div>
          </div>
          <div class="card card-static mb-3">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Online Presence</div>
            <div class="card-body">
              <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="{{ old('website',$profile?->website) }}" placeholder="https://yourwebsite.com"></div>
              <div class="form-group"><label class="form-label">YouTube Channel</label><input type="url" name="youtube_channel" class="form-control" value="{{ old('youtube_channel',$profile?->youtube_channel) }}" placeholder="https://youtube.com/@yourchannel"></div>
              <div class="form-group" style="margin-bottom:0"><label class="form-label">LinkedIn</label><input type="url" name="linkedin" class="form-control" value="{{ old('linkedin',$profile?->linkedin) }}" placeholder="https://linkedin.com/in/yourprofile"></div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg">Save Profile</button>
        </form>
        <aside style="position:sticky;top:80px">
          <div class="card card-static card-body text-center">
            <div style="width:70px;height:70px;border-radius:50%;background:var(--saffron);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;margin:0 auto 1rem">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
            <div style="font-weight:600;font-family:var(--fu)">{{ auth()->user()->name }}</div>
            @if($profile?->username)<div class="text-muted" style="font-size:.82rem">@{{ $profile->username }}</div>@endif
            @if($profile)<a href="{{ route('professor.profile',$profile->username) }}" class="btn btn-ghost btn-sm w-full mt-3" style="justify-content:center" target="_blank">View Public Profile ↗</a>@endif
            <div class="border-top mt-3" style="font-size:.82rem;color:var(--ink-l);line-height:1.8">
              <div>{{ $profile?->total_sales ?? 0 }} sales</div>
              <div>{{ $profile?->examPapers()->where('status','approved')->count() ?? 0 }} papers live</div>
              @if($profile?->is_verified)<div class="badge badge-teal mt-1">✓ Verified</div>@endif
            </div>
          </div>
        </aside>
      </div>
    </main>
  </div>
</div>
@endsection
