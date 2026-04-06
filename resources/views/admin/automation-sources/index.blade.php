@extends('layouts.app')
@section('title','Automation Sources — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Automation Sources</h2>
          <p class="text-muted" style="margin:0">Manage n8n-connected source pages, paper listing URLs, parser hints, and answer-key behavior from one place.</p>
        </div>
      </div>

      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($errors->any())
      <div class="alert alert-danger mb-3">
        <ul style="margin:0;padding-left:1.2rem">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
      @endif

      <div class="g-grid" style="grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1rem">
        <div class="card card-static card-body"><div class="text-muted" style="font-size:.8rem">Tracked Sources</div><div style="font-size:1.5rem;font-weight:700">{{ number_format($stats['sources']) }}</div></div>
        <div class="card card-static card-body"><div class="text-muted" style="font-size:.8rem">Active Sources</div><div style="font-size:1.5rem;font-weight:700">{{ number_format($stats['active_sources']) }}</div></div>
        <div class="card card-static card-body"><div class="text-muted" style="font-size:.8rem">Imported Blog Posts</div><div style="font-size:1.5rem;font-weight:700">{{ number_format($stats['imported_posts']) }}</div></div>
        <div class="card card-static card-body"><div class="text-muted" style="font-size:.8rem">Imported Leads</div><div style="font-size:1.5rem;font-weight:700">{{ number_format($stats['imported_leads']) }}</div></div>
      </div>

      <div class="g-grid" style="grid-template-columns:1.25fr .95fr;gap:1rem;align-items:start">
        <section class="card card-static">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)">
            <h3 style="font-size:1rem">Configured Sources</h3>
          </div>
          <div class="tbl-wrap" style="border:none;border-radius:0">
            <table class="tbl">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>URLs</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($sources as $source)
                <tr>
                  <td class="text-muted">{{ $source->subject ?: '—' }}</td>
                  <td>
                    <div style="font-weight:600;font-family:var(--fu)">{{ $source->name }}</div>
                    @if($source->discovery_query)
                    <div class="text-muted" style="font-size:.8rem;margin-top:.2rem">{{ $source->discovery_query }}</div>
                    @endif
                    @if($source->listing_page_url)
                    <div class="text-muted" style="font-size:.78rem;margin-top:.35rem">Paper listing configured</div>
                    @endif
                  </td>
                  <td><span class="badge badge-gray">{{ strtoupper($source->source_type) }}</span></td>
                  <td>
                    <div class="text-muted" style="font-size:.8rem;display:grid;gap:.2rem">
                      @if($source->base_url)<a href="{{ $source->base_url }}" target="_blank" rel="noopener">Site</a>@endif
                      @if($source->rss_url)<a href="{{ $source->rss_url }}" target="_blank" rel="noopener">RSS</a>@endif
                      @if($source->listing_page_url)<a href="{{ $source->listing_page_url }}" target="_blank" rel="noopener">Paper Listing</a>@endif
                      @if($source->answer_key_listing_url)<a href="{{ $source->answer_key_listing_url }}" target="_blank" rel="noopener">Answer Key Listing</a>@endif
                    </div>
                  </td>
                  <td>
                    <span class="badge {{ $source->is_active ? 'badge-green' : 'badge-gray' }}">{{ $source->is_active ? 'Active' : 'Paused' }}</span>
                    <div class="text-muted" style="font-size:.78rem;margin-top:.3rem">PDF: {{ $source->pdf_kind === 'scanned' ? 'Scanned/OCR' : 'Text PDF' }} · Answers: {{ ucwords(str_replace('_',' ', $source->answer_key_mode)) }}</div>
                    @if($source->last_checked_at)
                    <div class="text-muted" style="font-size:.78rem;margin-top:.3rem">Checked {{ $source->last_checked_at->format('d M Y') }}</div>
                    @endif
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-muted text-center" style="padding:2rem">No automation sources have been synced or added yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-l)">{{ $sources->links() }}</div>
        </section>

        <section style="display:grid;gap:1rem">
          <div class="card card-static card-body">
            <h3 style="font-size:1rem;margin-bottom:.6rem">n8n Connection</h3>
            <div class="text-muted" style="font-size:.84rem;line-height:1.6">
              Use `X-N8N-Token` with the shared token from `.env` for import endpoints and run logs. PDFs can land as draft exams first, then the admin team reviews metadata and manually starts parsing.
            </div>
            <pre style="margin-top:1rem;white-space:pre-wrap;font-size:.76rem;line-height:1.5;background:var(--paper);padding:1rem;border-radius:14px">GET  /api/v1/automation/bootstrap
POST /api/v1/automation/sources/sync
POST /api/v1/automation/blog/import
POST /api/v1/automation/professor-leads/import</pre>
          </div>

          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Recent Automation Runs</h3></div>
            <div style="padding:1rem 1.25rem;display:grid;gap:.8rem">
              @forelse($recentRuns as $run)
              <article style="border:1px solid var(--border-l);border-radius:14px;padding:.85rem 1rem;background:#fff">
                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start">
                  <div>
                    <div style="font-weight:600;font-family:var(--fu)">{{ $run->workflow_name }}</div>
                    <div class="text-muted" style="font-size:.8rem;margin-top:.2rem">{{ $run->message ?: 'Automation run completed.' }}</div>
                  </div>
                  <span class="badge {{ $run->status === 'processed' ? 'badge-green' : ($run->status === 'failed' ? 'badge-red' : 'badge-gray') }}">{{ ucfirst($run->status) }}</span>
                </div>
                <div class="text-muted" style="font-size:.78rem;margin-top:.45rem">{{ $run->created_at->format('d M Y, h:i A') }} · {{ number_format($run->processed_count) }} item(s)</div>
              </article>
              @empty
              <div class="text-muted">No n8n runs logged yet.</div>
              @endforelse
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection
