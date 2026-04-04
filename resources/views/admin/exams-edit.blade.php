@extends('layouts.app')
@section('title','Edit Exam — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main style="max-width:820px;width:100%">
      <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.exams.index') }}" style="font-size:.85rem;color:var(--ink-l)">← Back to Manage Exams</a>
        <h2 class="mt-1">Edit Exam</h2>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-error mb-3">{{ session('error') }}</div>@endif
      @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif

  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">TAO Integration</div>
    <div class="card-body" style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap">
      <div>
        <div class="text-muted" style="font-size:.82rem">Sync Status</div>
        <div style="font-weight:600;margin-top:.25rem">{{ ucfirst($paper->tao_sync_status ?? 'pending') }}</div>
        <div class="text-muted" style="font-size:.82rem;margin-top:.5rem">TAO Test ID: {{ $paper->tao_test_id ?: 'Not synced yet' }}</div>
        <div class="text-muted" style="font-size:.82rem">TAO Delivery ID: {{ $paper->tao_delivery_id ?: 'Not created yet' }}</div>
        @if($paper->tao_last_error)
          <div class="alert alert-error mt-2" style="font-size:.82rem">{{ $paper->tao_last_error }}</div>
        @endif
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <form action="{{ route('admin.exams.sync-tao', $paper) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-outline btn-sm">Sync to TAO</button>
        </form>
        @if(config('services.tao.url'))
          <a href="{{ config('services.tao.url') }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Open TAO</a>
        @endif
      </div>
    </div>
  </div>

  <div class="card card-static mb-3">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">TAO Sync History</div>
    <div class="card-body" style="padding:0">
      @if($paper->taoSyncLogs->count())
        <div class="tbl-wrap" style="margin:0">
          <table class="tbl">
            <thead>
              <tr>
                <th>When</th>
                <th>By</th>
                <th>Trigger</th>
                <th>Status</th>
                <th>Message</th>
              </tr>
            </thead>
            <tbody>
              @foreach($paper->taoSyncLogs->take(10) as $log)
                <tr>
                  <td class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                  <td>{{ $log->user->name ?? 'System' }}</td>
                  <td><span class="badge badge-gray">{{ ucfirst($log->trigger) }}</span></td>
                  <td><span class="badge {{ $log->status === 'success' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($log->status) }}</span></td>
                  <td>
                    <div>{{ $log->message ?: '—' }}</div>
                    @if($log->tao_test_id || $log->tao_delivery_id)
                      <div class="text-muted" style="font-size:.78rem;margin-top:.25rem">
                        Test: {{ $log->tao_test_id ?: '—' }} · Delivery: {{ $log->tao_delivery_id ?: '—' }}
                      </div>
                    @endif
                    @if(!empty($log->request_payload) || !empty($log->response_payload))
                      <details style="margin-top:.5rem">
                        <summary style="cursor:pointer;color:var(--teal);font-size:.82rem;font-family:var(--fu)">View full payload</summary>
                        <div style="display:grid;grid-template-columns:1fr;gap:.5rem;margin-top:.6rem">
                          @if(!empty($log->request_payload))
                            <div>
                              <div class="text-muted" style="font-size:.75rem;margin-bottom:.2rem">Request</div>
                              <pre style="margin:0;white-space:pre-wrap;word-break:break-word;background:var(--border-l);padding:.75rem;border-radius:var(--r1);font-size:.76rem;line-height:1.5">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                          @endif
                          @if(!empty($log->response_payload))
                            <div>
                              <div class="text-muted" style="font-size:.75rem;margin-bottom:.2rem">Response</div>
                              <pre style="margin:0;white-space:pre-wrap;word-break:break-word;background:var(--border-l);padding:.75rem;border-radius:var(--r1);font-size:.76rem;line-height:1.5">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                          @endif
                        </div>
                      </details>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div style="padding:1rem 1.25rem" class="text-muted">No TAO sync attempts have been recorded for this exam yet.</div>
      @endif
    </div>
  </div>

  <form action="{{ route('admin.exams.update',$paper) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card card-static mb-3">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Basic Information</div>
      <div class="card-body">
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title',$paper->title) }}" required></div>
        <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject',$paper->subject) }}"></div>
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control" required>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id',$paper->category_id)==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Language</label>
            <select name="language" class="form-control">
              @foreach(['English','Hindi','Both'] as $lang)
                <option value="{{ $lang }}" {{ old('language',$paper->language)===$lang?'selected':'' }}>{{ $lang }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0">
            <label class="form-label">Exam Type</label>
            <select name="exam_type" class="form-control">
              <option value="mock" {{ old('exam_type',$paper->exam_type)==='mock'?'selected':'' }}>Mock Exam Paper</option>
              <option value="previous_year" {{ old('exam_type',$paper->exam_type)==='previous_year'?'selected':'' }}>Old Exam Paper (PYQ)</option>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              @foreach(['draft','pending_review','approved','rejected'] as $status)
                <option value="{{ $status }}" {{ old('status',$paper->status)===$status?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description',$paper->description) }}</textarea></div>
        <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" value="{{ old('tags',implode(', ',$paper->tags??[])) }}"></div>
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
          <div class="form-group" style="margin:0"><label class="form-label">Difficulty</label><select name="difficulty" class="form-control"><option value="easy" {{ old('difficulty',$paper->difficulty)==='easy'?'selected':'' }}>Easy</option><option value="medium" {{ old('difficulty',$paper->difficulty)==='medium'?'selected':'' }}>Medium</option><option value="hard" {{ old('difficulty',$paper->difficulty)==='hard'?'selected':'' }}>Hard</option></select></div>
          <div class="form-group" style="margin:0"><label class="form-label">Max Retakes</label><input type="number" name="max_retakes" class="form-control" value="{{ old('max_retakes',$paper->max_retakes) }}" min="1" max="10"></div>
        </div>
      </div>
    </div>

    <div class="card card-static mb-4">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">Pricing</div>
      <div class="card-body">
        <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="form-group" style="margin:0"><label class="form-label">Seller Price (₹)</label><input type="number" name="seller_price" class="form-control" value="{{ old('seller_price',$paper->seller_price) }}" min="0"></div>
          <div class="form-group" style="margin:0"><label class="form-label">Free Access</label><select name="is_free" class="form-control"><option value="0" {{ !old('is_free',$paper->is_free)?'selected':'' }}>No</option><option value="1" {{ old('is_free',$paper->is_free)?'selected':'' }}>Yes</option></select></div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Exam</button>
  </form>
    </main>
  </div>
</div>
@endsection
