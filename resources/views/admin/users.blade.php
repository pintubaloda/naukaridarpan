@extends('layouts.app')
@section('title','Users — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">All Users</h2>
      <form action="{{ route('admin.users') }}" method="GET" style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap">
        <input type="search" name="search" class="form-control" style="max-width:260px" placeholder="Search name or email…" value="{{ request('search') }}">
        <select name="role" class="form-control" style="max-width:160px" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <option value="student" {{ request('role')=='student'?'selected':'' }}>Students</option>
          <option value="seller" {{ request('role')=='seller'?'selected':'' }}>Sellers</option>
          <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admins</option>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
      </form>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Status</th><th></th></tr></thead>
          <tbody>
            @foreach($users as $u)
            <tr>
              <td style="font-weight:500;font-family:var(--fu)">{{ $u->name }}</td>
              <td class="text-muted">{{ $u->email }}</td>
              <td><span class="badge {{ ['student'=>'badge-teal','seller'=>'badge-saffron','admin'=>'badge-gold'][$u->role]??'badge-gray' }}">{{ ucfirst($u->role) }}</span></td>
              <td class="text-muted">{{ $u->created_at->format('d M Y') }}</td>
              <td><span class="badge {{ $u->is_active?'badge-green':'badge-red' }}">{{ $u->is_active?'Active':'Blocked' }}</span></td>
              <td>
                @if($u->id !== auth()->id())
                <form action="{{ route('admin.users.toggle',$u) }}" method="POST">@csrf<button type="submit" class="btn btn-ghost btn-sm">{{ $u->is_active?'Block':'Unblock' }}</button></form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:1.5rem">{{ $users->links() }}</div>
    </main>
  </div>
</div>
@endsection
