@extends('layouts.app')
@section('title','QTI Packages — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">QTI Packages</h2>
          <p class="text-muted" style="margin:0">Manage the QTI import/export foundation, package history, and package-level summaries.</p>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:380px 1fr;gap:1rem;align-items:start">
        <section style="display:grid;gap:1rem">
          <div class="card card-static card-body">
            <h3 style="font-size:1rem;margin-bottom:1rem">Import Package</h3>
            <form method="POST" action="{{ route('admin.qti.import') }}" enctype="multipart/form-data">
              @csrf
              <label class="label">Package File</label>
              <input type="file" name="package_file" class="input" accept=".zip,.xml,.json" required>
              <div class="text-muted" style="font-size:.8rem;margin-top:.35rem">Accepts `.zip`, `.xml`, or `.json` foundation packages.</div>
              <button class="btn btn-primary" type="submit" style="margin-top:1rem;width:100%">Import QTI Package</button>
            </form>
          </div>

          <div class="card card-static card-body">
            <h3 style="font-size:1rem;margin-bottom:1rem">Export Exam</h3>
            <form method="POST" action="{{ route('admin.qti.export') }}">
              @csrf
              <label class="label">Approved Exam</label>
              <select name="exam_paper_id" class="input" required>
                <option value="">Select exam</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                @endforeach
              </select>
              <button class="btn btn-outline" type="submit" style="margin-top:1rem;width:100%">Create Export Package</button>
            </form>
          </div>
        </section>

        <section>
          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)">
              <h3 style="font-size:1rem">Package History</h3>
            </div>
            <div class="tbl-wrap" style="border:none;border-radius:0">
              <table class="tbl">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Direction</th>
                    <th>Status</th>
                    <th>Version</th>
                    <th>Exam</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($packages as $package)
                  <tr>
                    <td>
                      <div style="font-weight:600;font-family:var(--fu)">{{ $package->name }}</div>
                      @if($package->manifest_identifier)
                      <div class="text-muted" style="font-size:.8rem;margin-top:.2rem">{{ $package->manifest_identifier }}</div>
                      @endif
                    </td>
                    <td><span class="badge badge-gray">{{ ucfirst($package->direction) }}</span></td>
                    <td><span class="badge {{ $package->status === 'processed' ? 'badge-green' : ($package->status === 'failed' ? 'badge-red' : 'badge-gold') }}">{{ ucfirst($package->status) }}</span></td>
                    <td class="text-muted">{{ $package->version }}</td>
                    <td class="text-muted">{{ $package->examPaper->title ?? 'Imported package' }}</td>
                    <td class="text-muted">{{ $package->updated_at->format('d M Y') }}</td>
                  </tr>
                  @if(!empty($package->summary))
                  <tr>
                    <td colspan="6" style="background:var(--paper)">
                      <pre style="margin:0;white-space:pre-wrap;font-size:.78rem;line-height:1.5">{{ json_encode($package->summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </td>
                  </tr>
                  @endif
                  @empty
                  <tr><td colspan="6" class="text-muted text-center" style="padding:2rem">No QTI packages yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-l)">{{ $packages->links() }}</div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection
