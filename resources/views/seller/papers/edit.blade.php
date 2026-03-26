@extends('layouts.app')
@section('title','Edit Paper — '.$paper->title)
@section('content')
<div class="container section" style="max-width:760px">
  <div style="margin-bottom:1.5rem"><a href="{{ route('seller.papers') }}" style="font-size:.85rem;color:var(--ink-l)">← Back to Papers</a><h2 class="mt-1">Edit Exam Paper</h2></div>
  @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif

  {{-- Parse status --}}
  <div class="parse-status {{ ['pending'=>'ps-pending','processing'=>'ps-processing','done'=>'ps-done','failed'=>'ps-failed'][$paper->parse_status]??'ps-pending' }} mb-3">
    <div class="spinner" style="{{ $paper->parse_status==='processing'?'':'display:none' }}"></div>
    <div>
      <strong>Parse Status: {{ ucfirst($paper->parse_status) }}</strong> — {{ $paper->total_questions }} questions extracted
      @if($paper->parse_log)<br><span style="font-size:.78rem;opacity:.75">{{ $paper->parse_log }}</span>@endif
    </div>
  </div>

  <form action="{{ route('seller.papers.update',$paper) }}" method="POST">@csrf @method('PUT')
    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Information</div>
      <div class="card-body">
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title',$paper->title) }}" required></div>
        <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject',$paper->subject) }}"></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description',$paper->description) }}</textarea></div>
        <div class="form-group"><label class="form-label">Tags <span class="form-hint" style="display:inline;margin:0">(comma-separated)</span></label><input type="text" name="tags" class="form-control" value="{{ old('tags',implode(', ',$paper->tags??[])) }}"></div>
      </div>
    </div>
    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Exam Settings</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:repeat(3,1fr);gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Duration (min)</label><input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes',$paper->duration_minutes) }}" min="10" required></div>
          <div class="form-group" style="margin:0"><label class="form-label">Total Marks</label><input type="number" name="max_marks" class="form-control" value="{{ old('max_marks',$paper->max_marks) }}" min="10" required></div>
          <div class="form-group" style="margin:0"><label class="form-label">Negative Marking</label><input type="number" name="negative_marking" class="form-control" value="{{ old('negative_marking',$paper->negative_marking) }}" step="0.25" min="0"></div>
        </div>
        <div class="g-grid mt-2" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Difficulty</label><select name="difficulty" class="form-control"><option value="easy" {{ $paper->difficulty=='easy'?'selected':'' }}>Easy</option><option value="medium" {{ $paper->difficulty=='medium'?'selected':'' }}>Medium</option><option value="hard" {{ $paper->difficulty=='hard'?'selected':'' }}>Hard</option></select></div>
          <div class="form-group" style="margin:0"><label class="form-label">Max Retakes</label><select name="max_retakes" class="form-control">@for($i=1;$i<=5;$i++)<option value="{{ $i }}" {{ $paper->max_retakes==$i?'selected':'' }}>{{ $i }}</option>@endfor</select></div>
        </div>
      </div>
    </div>
    <div class="card card-static mb-4">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Pricing</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Your Price (₹)</label><input type="number" name="seller_price" class="form-control" value="{{ old('seller_price',$paper->seller_price) }}" min="0"></div>
          <div class="form-group" style="margin:0"><label class="form-label">Student Pays</label><input type="text" class="form-control" value="₹{{ number_format($paper->student_price,0) }} (incl. 15% fee)" disabled style="opacity:.7"></div>
        </div>
        <div class="form-group mt-2" style="margin-bottom:0"><label class="form-check"><input type="checkbox" name="is_free" value="1" {{ $paper->is_free?'checked':'' }}> <span style="font-size:.88rem">Make free for all students</span></label></div>
      </div>
    </div>
    <div style="display:flex;gap:1rem">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      @if($paper->parse_status==='done' && $paper->status==='draft')
      <form action="{{ route('seller.papers.submit',$paper) }}" method="POST">@csrf<button type="submit" class="btn btn-teal">Submit for Review →</button></form>
      @endif
      <form action="{{ route('seller.papers.destroy',$paper) }}" method="POST" onsubmit="return confirm('Delete this paper? This cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Delete</button></form>
    </div>
  </form>
</div>
@push('scripts')
<script>
@if($paper->parse_status === 'processing')
const poll = setInterval(async()=>{
  const r = await fetch('{{ route('seller.papers.parse-status',$paper) }}');
  const d = await r.json();
  if(d.status!=='processing'){clearInterval(poll);location.reload();}
},4000);
@endif
</script>
@endpush
@endsection
