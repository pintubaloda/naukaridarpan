@extends('layouts.app')
@section('title','Interoperability — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Interoperability</h2>
          <p class="text-muted" style="margin:0">Manage external assessment endpoints and review the payload foundation we can exchange with other systems.</p>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:380px 1fr;gap:1rem;align-items:start">
        <section class="card card-static card-body">
          <h3 style="font-size:1rem;margin-bottom:1rem">Add Integration</h3>
          <form method="POST" action="{{ route('admin.interoperability.store') }}">
            @csrf
            <input name="name" class="input" placeholder="Integration name" required>
            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem">
              <input name="integration_type" class="input" value="lti" placeholder="Type" required>
              <input name="auth_type" class="input" value="bearer" placeholder="Auth type" required>
            </div>
            <div style="margin-top:.75rem">
              <input name="endpoint_url" class="input" placeholder="https://partner.example.com/api" required>
            </div>
            <div style="margin-top:.75rem">
              <textarea name="configuration_text" class="input" rows="4" placeholder="client_id: demo&#10;audience: example"></textarea>
            </div>
            <label style="display:flex;align-items:center;gap:.5rem;margin-top:.9rem">
              <input type="checkbox" name="is_active" value="1" checked>
              <span>Active</span>
            </label>
            <button class="btn btn-primary" type="submit" style="margin-top:1rem;width:100%">Save Integration</button>
          </form>
        </section>

        <section style="display:grid;gap:1rem">
          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Integration Registry</h3></div>
            <div style="padding:1rem 1.25rem;display:grid;gap:1rem">
              @forelse($integrations as $integration)
              <article style="border:1px solid var(--border-l);border-radius:16px;padding:1rem;background:#fff">
                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start">
                  <div>
                    <div style="font-weight:600;font-family:var(--fu)">{{ $integration->name }}</div>
                    <div class="text-muted" style="font-size:.82rem;margin-top:.25rem">{{ strtoupper($integration->integration_type) }} · {{ $integration->endpoint_url }}</div>
                  </div>
                  <span class="badge {{ $integration->is_active ? 'badge-green' : 'badge-gray' }}">{{ $integration->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @if(!empty($integration->configuration))
                <pre style="margin-top:.75rem;white-space:pre-wrap;font-size:.78rem;line-height:1.5;background:var(--paper);padding:.75rem;border-radius:12px">{{ json_encode($integration->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                @endif
                <div style="display:flex;justify-content:space-between;gap:.75rem;align-items:center;flex-wrap:wrap;margin-top:1rem">
                  <details>
                    <summary style="cursor:pointer;color:var(--teal);font-family:var(--fu);font-size:.88rem">Edit Integration</summary>
                    <form method="POST" action="{{ route('admin.interoperability.update', $integration) }}" style="margin-top:.8rem;display:grid;gap:.65rem;min-width:min(520px, 100%)">
                      @csrf
                      @method('PUT')
                      <input name="name" class="input" value="{{ $integration->name }}" required>
                      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.65rem">
                        <input name="integration_type" class="input" value="{{ $integration->integration_type }}" required>
                        <input name="auth_type" class="input" value="{{ $integration->auth_type }}" required>
                      </div>
                      <input name="endpoint_url" class="input" value="{{ $integration->endpoint_url }}" required>
                      <textarea name="configuration_text" class="input" rows="4">{{ collect($integration->configuration ?? [])->map(fn ($value, $key) => $key.': '.$value)->implode("\n") }}</textarea>
                      <label style="display:flex;align-items:center;gap:.5rem">
                        <input type="checkbox" name="is_active" value="1" {{ $integration->is_active ? 'checked' : '' }}>
                        <span>Active</span>
                      </label>
                      <div style="display:flex;justify-content:flex-end">
                        <button type="submit" class="btn btn-outline btn-sm">Save Changes</button>
                      </div>
                    </form>
                  </details>
                  <form method="POST" action="{{ route('admin.interoperability.destroy', $integration) }}" onsubmit="return confirm('Delete this integration?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--err)">Delete</button>
                  </form>
                </div>
              </article>
              @empty
              <div class="text-muted" style="padding:2rem;text-align:center">No interoperability integrations yet.</div>
              @endforelse
            </div>
          </div>

          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)"><h3 style="font-size:1rem">Payload Preview</h3></div>
            <div class="card-body">
              @if($previewPayload)
              <div class="text-muted" style="font-size:.84rem;margin-bottom:.75rem">Preview based on the latest approved exam{{ $sampleExam ? ': '.$sampleExam->title : '' }}.</div>
              <pre style="margin:0;white-space:pre-wrap;font-size:.78rem;line-height:1.5;background:var(--paper);padding:1rem;border-radius:14px">{{ json_encode($previewPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
              @else
              <div class="text-muted">No approved exam available yet for interoperability preview.</div>
              @endif
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection
