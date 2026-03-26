@extends('layouts.app')
@section('title', $category->name.' Mock Tests — Naukaridarpan')
@section('content')
<div style="background:var(--teal);padding:2.5rem 0 2rem">
  <div class="container">
    <a href="{{ route('home') }}" style="color:rgba(255,255,255,.6);font-size:.82rem;font-family:var(--fu)">← Home</a>
    <h1 style="color:#fff;margin-top:.5rem;margin-bottom:.4rem">{{ $category->name }} Mock Tests</h1>
    @if($category->description)<p style="color:rgba(255,255,255,.75);font-size:.95rem">{{ $category->description }}</p>@endif
    <p style="color:rgba(255,255,255,.55);font-size:.82rem;font-family:var(--fu);margin-top:.5rem">{{ $exams->total() }} papers available</p>
  </div>
</div>
<div class="container" style="padding:2rem 1.25rem 4rem">
  @if($exams->count())
    <div class="exam-grid">@foreach($exams as $exam)@include('components.exam-card',['exam'=>$exam])@endforeach</div>
    <div style="margin-top:2rem">{{ $exams->links() }}</div>
  @else
    <div class="card card-static card-body text-center" style="padding:4rem 2rem;margin-top:2rem">
      <div style="font-size:3rem;margin-bottom:1rem">📭</div>
      <h3>No papers in this category yet</h3>
      <p class="mt-2">Be the first educator to <a href="{{ route('register.seller') }}">upload a paper</a> here.</p>
    </div>
  @endif
</div>
@endsection
