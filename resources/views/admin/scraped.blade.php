@extends('layouts.app')
@section('title','Scraped Papers — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">Scraped PYQ Papers</h2>
      <p class="text-muted mb-4">Auto-scraped previous year papers awaiting publication</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($papers->count())
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>Title</th><th>Category</th><th>Parse Status</th><th>Scraped</th><th></th></tr></thead>
          <tbody>
            @foreach($papers as $p)
            <tr>
              <td style="font-weight:500;font-family:var(--fu);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->title }}</td>
              <td class="text-muted">{{ $p->category->name }}</td>
              <td><span class="parse-status {{ ['done'=>'ps-done','failed'=>'ps-failed','processing'=>'ps-processing','pending'=>'ps-pending'][$p->parse_status]??'ps-pending' }}" style="padding:.2rem .6rem;font-size:.72rem">{{ ucfirst($p->parse_status) }} · {{ $p->total_questions }} Qs</span></td>
              <td class="text-muted">{{ $p->created_at->format('d M Y') }}</td>
              <td>
                <div style="display:flex;gap:.5rem;justify-content:flex-end;align-items:center;flex-wrap:wrap">
                  @if($p->parse_status==='done')
                  <a href="{{ route('admin.exams.edit',$p) }}" class="btn btn-ghost btn-sm">Open Draft</a>
                  @else<span class="text-muted" style="font-size:.82rem">Parsing…</span>@endif
                  <form method="POST" action="{{ route('admin.scraped.destroy',$p) }}" onsubmit="return confirm('Delete this scraped draft paper? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--err)">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:1.5rem">{{ $papers->links() }}</div>
      @else
      <div class="card card-static card-body text-center" style="padding:4rem"><div style="font-size:3rem;margin-bottom:1rem">🔍</div><h3>No scraped papers yet</h3><p class="mt-2 text-muted">Run <code>php artisan scrape:papers --parse</code> to start scraping.</p></div>
      @endif
    </main>
  </div>
</div>
@endsection
