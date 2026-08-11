<x-app-layout title="Pengguna">
@push('scripts')@vite('resources/js/users.js')@endpush
<div class="category-page users-page">
<nav><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Pengaturan</li><li class="breadcrumb-item active">Pengguna</li></ol></nav>
<header class="category-page-header"><div><h1>Pengguna</h1><p>Kelola akun dan hak akses pengguna sistem.</p></div><button class="btn category-primary-btn" data-bs-toggle="modal" data-bs-target="#createUserModal"><i data-lucide="plus"></i>Tambah Pengguna</button></header>
<section class="category-mini-summary users-summary"><div><span>Total Pengguna</span><strong>{{ $totalUsers }}</strong></div><i></i><div><span>Pengguna Aktif</span><strong>{{ $activeUsers }}</strong></div><i></i><div><span>Admin / Operator</span><strong>{{ $adminUsers }}</strong></div><i></i><div><span>Kepala Sekolah</span><strong>{{ $headUsers }}</strong></div></section>
@if($errors->has('account'))<div class="alert alert-danger">{{ $errors->first('account') }}</div>@endif
<section class="category-panel">
<form method="GET" class="asset-toolbar users-toolbar"><div class="category-search asset-search"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau username..."></div><div class="asset-filters"><select name="role"><option value="">Semua Role</option><option value="admin" @selected(request('role')==='admin')>Admin / Operator</option><option value="kepala_sekolah" @selected(request('role')==='kepala_sekolah')>Kepala Sekolah</option></select><select name="status"><option value="">Semua Status</option><option value="active" @selected(request('status')==='active')>Aktif</option><option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option></select></div><div class="asset-filter-actions"><button class="btn category-search-btn">Filter</button>@if(request()->hasAny(['search','role','status']))<a class="category-reset" href="{{ route('users.index') }}"><i data-lucide="rotate-ccw"></i>Reset</a>@endif</div></form>
@if($users->isEmpty())
<div class="category-empty"><div><i data-lucide="users"></i></div><h2>Belum ada pengguna</h2><p>Tambahkan pengguna untuk memberikan akses ke sistem.</p></div>
@else
<div class="category-table-wrap"><table class="category-table users-table"><thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Terakhir Login</th><th class="text-end">Aksi</th></tr></thead><tbody>
@foreach($users as $user)
<tr><td><div class="user-name-cell"><span>{{ strtoupper(substr($user->name,0,2)) }}</span><b>{{ $user->name }}</b>@if(auth()->id()===$user->id)<small>Akun Anda</small>@endif</div></td><td><b>{{ $user->username }}</b></td><td>{{ $user->email ?: '—' }}</td><td><span class="user-role {{ $user->role }}">{{ $user->roleLabel() }}</span></td><td><span class="user-status {{ $user->is_active?'active':'inactive' }}">{{ $user->is_active?'Aktif':'Nonaktif' }}</span></td><td>{{ $user->last_login_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum pernah login' }}</td><td class="text-end"><div class="dropdown"><button class="category-action" data-bs-toggle="dropdown"><i data-lucide="ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end category-action-menu">
<li><button class="dropdown-item edit-user" data-bs-toggle="modal" data-bs-target="#editUserModal" data-url="{{ route('users.update',$user) }}" data-name="{{ $user->name }}" data-username="{{ $user->username }}" data-email="{{ $user->email }}" data-role="{{ $user->role }}" data-active="{{ $user->is_active?'1':'0' }}"><i data-lucide="pencil"></i>Edit</button></li>
<li><button class="dropdown-item password-user" data-bs-toggle="modal" data-bs-target="#passwordUserModal" data-url="{{ route('users.password',$user) }}" data-name="{{ $user->name }}"><i data-lucide="lock-keyhole"></i>Ubah Password</button></li>
<li><form method="POST" action="{{ route('users.toggle',$user) }}">@csrf @method('PATCH')<button class="dropdown-item {{ $user->is_active?'text-danger':'' }}" @disabled(auth()->id()===$user->id)><i data-lucide="power"></i>{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}</button></form></li>
</ul></div></td></tr>
@endforeach
</tbody></table></div>
<div class="category-pagination">
    <div class="pagination-wrapper">
        <span>Tampilkan:</span>
        <select class="per-page-select" onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();">
            <option value="10" @selected(request('per_page', 10) == 10)>10</option>
            <option value="20" @selected(request('per_page') == 20)>20</option>
            <option value="50" @selected(request('per_page') == 50)>50</option>
            <option value="100" @selected(request('per_page') == 100)>100</option>
        </select>
        <span>Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna</span>
    </div>
    {{ $users->links() }}
</div>
@endif
</section></div>
@include('users.partials.modals')
</x-app-layout>
