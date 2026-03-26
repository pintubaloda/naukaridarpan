@extends('layouts.app')
@section('title','My Papers — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div><h2 class="mb-1">My Papers</h2><p class="text-muted">All your uploaded exam papers</p></div>
        <a href="{{ route('seller.papers.create') }}" class="btn btn-primary">+ Upload New Paper</a>
      </div>
      @if($papers->count())
        <div class="tbl-wrap">
          <table class="tbl">
            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Parse</th><th>Price</th><th>Sales</th><th>Actions</th></tr></thead>
            <tbody>
              @foreach($papers as $paper)
              <tr>
                <td style="font-weight:500;font-family:var(--fu);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $paper->title }}</td>
                <td class="text-muted">{{ $paper->category->name }}</td>
                <td>
                  @php $sc=['draft'=>'badge-gray','pending_review'=>'badge-gold','approved'=>'badge-green','rejected'=>'badge-red','archived'=>'badge-gray'][$paper->status]??'badge-gray'; @endphp
                  <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_',' ',$paper->status)) }}</span>
                </td>
                <td>
                  @php $pc=['pending'=>'ps-pending','processing'=>'ps-processing','done'=>'ps-done','failed'=>'ps-failed'][$paper->parse_status]??'ps-pending'; @endphp
                  <span class="parse-status {{ $pc }}" style="padding:.2rem .6rem;font-size:.72rem">{{ ucfirst($paper->parse_status) }}</span>
                </td>
                <td class="fw-600">{{ $paper->is_free?'Free':'₹'.number_format($paper->student_price,0) }}</td>
                <td>{{ $paper->total_purchases }}</td>
                <td>
                  <div style="display:flex;gap:.4rem">
                    <a href="{{ route('seller.papers.edit',$paper) }}" class="btn btn-ghost btn-sm">Edit</a>
                    @if($paper->parse_status==='done' && $paper->status==='draft')
                    <form action="{{ route('seller.papers.submit',$paper) }}" method="POST">@csrf<button type="submit" class="btn btn-teal btn-sm">Submit</button></form>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:1.5rem">{{ $papers->links() }}</div>
      @else
        <div class="card card-static card-body text-center" style="padding:4rem 2rem">
          <div style="font-size:3rem;margin-bottom:1rem">📄</div>
          <h3>No papers yet</h3>
          <p class="mt-2 mb-3">Upload your first exam paper to start earning.</p>
          <a href="{{ route('seller.papers.create') }}" class="btn btn-primary">Upload Paper</a>
        </div>
      @endif
    </main>
  </div>
</div>
@endsection
