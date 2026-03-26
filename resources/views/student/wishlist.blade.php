@extends('layouts.app')
@section('title', 'My Wishlist — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.student-sidebar')
    <main>
      <h2 class="mb-1">My Wishlist</h2>
      <p class="text-muted mb-4">Exams you've saved to buy later</p>
      <div class="card card-static card-body text-center" style="padding:4rem 2rem">
        <div style="font-size:3rem;margin-bottom:1rem">🔖</div>
        <h3>Your wishlist is empty</h3>
        <p class="mt-2 mb-3">Browse exams and save the ones you're interested in.</p>
        <a href="{{ route('exams.browse') }}" class="btn btn-primary">Browse Exams</a>
      </div>
    </main>
  </div>
</div>
@endsection
