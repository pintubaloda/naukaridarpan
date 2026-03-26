@extends('layouts.app')
@section('title','Professor Leads — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div><h2 class="mb-1">Professor Leads CRM</h2><p class="text-muted">Educator contacts scraped for onboarding</p></div>
        <form action="{{ route('admin.professor-leads.mail') }}" method="POST" id="mailer-form">@csrf
          <input type="hidden" name="template" value="invite">
          <button type="submit" class="btn btn-primary" onclick="collectLeads()">Send Invite Mailer to Selected</button>
        </form>
      </div>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th><input type="checkbox" id="select-all" onchange="toggleAll(this)"></th><th>Name</th><th>Email</th><th>Platform</th><th>Subject</th><th>Status</th><th>Emails Sent</th></tr></thead>
          <tbody>
            @foreach($leads as $lead)
            <tr>
              <td><input type="checkbox" class="lead-cb" value="{{ $lead->id }}"></td>
              <td style="font-weight:500;font-family:var(--fu)">{{ $lead->name ?? '—' }}</td>
              <td class="text-muted">{{ $lead->email ?? '—' }}</td>
              <td><span class="badge badge-gray">{{ $lead->platform ?? '—' }}</span></td>
              <td class="text-muted">{{ Str::limit($lead->subject,30) }}</td>
              <td><span class="badge {{ ['new'=>'badge-gray','emailed'=>'badge-teal','replied'=>'badge-gold','onboarded'=>'badge-green','rejected'=>'badge-red'][$lead->outreach_status]??'badge-gray' }}">{{ ucfirst($lead->outreach_status) }}</span></td>
              <td class="text-muted">{{ $lead->email_count }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:1.5rem">{{ $leads->links() }}</div>
    </main>
  </div>
</div>
@push('scripts')
<script>
function toggleAll(cb){ document.querySelectorAll('.lead-cb').forEach(c=>c.checked=cb.checked); }
function collectLeads(){
  const ids=[...document.querySelectorAll('.lead-cb:checked')].map(c=>c.value);
  if(!ids.length){alert('Select at least one lead.');return false;}
  ids.forEach(id=>{const i=document.createElement('input');i.type='hidden';i.name='lead_ids[]';i.value=id;document.getElementById('mailer-form').appendChild(i);});
}
</script>
@endpush
@endsection
