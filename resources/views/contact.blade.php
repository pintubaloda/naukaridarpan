@extends('layouts.app')
@section('title','Contact Us — Naukaridarpan')
@section('content')
<div class="container" style="padding:3rem 1.25rem 5rem;max-width:900px">
  <div class="text-center mb-4"><h1>Contact Us</h1><p class="text-muted">We typically respond within 24 hours</p></div>
  <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:3rem;align-items:start">
    <div>
      @foreach([['📧','Email','support@naukaridarpan.com'],['📱','WhatsApp','+91-9876543210'],['🕐','Hours','Mon–Sat, 9 AM – 7 PM IST'],['📍','Office','New Delhi, India']] as [$i,$l,$v])
      <div style="display:flex;gap:1rem;margin-bottom:1.5rem">
        <div style="width:44px;height:44px;border-radius:var(--r2);background:var(--saffron-l);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">{{ $i }}</div>
        <div><div style="font-weight:600;font-size:.88rem;font-family:var(--fu);margin-bottom:.15rem">{{ $l }}</div><div style="font-size:.9rem;color:var(--ink-m)">{{ $v }}</div></div>
      </div>
      @endforeach
    </div>
    <div class="card card-static card-body">
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <form action="{{ route('contact.submit') }}" method="POST">@csrf
        <div class="form-group"><label class="form-label">Your Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Priya Sharma" required></div>
        <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required></div>
        <div class="form-group">
          <label class="form-label">Topic</label>
          <select name="topic" class="form-control">
            <option>General Enquiry</option><option>Payment Issue</option><option>Exam / Technical Issue</option><option>Seller / Payout Support</option><option>KYC Verification</option><option>Report Content</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5" placeholder="Describe your issue or question in detail…" required>{{ old('message') }}</textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
      </form>
    </div>
  </div>
</div>
@endsection
