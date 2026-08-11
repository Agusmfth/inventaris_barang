<x-app-layout title="Sumber Dana">
<div class="category-page">
    <nav aria-label="breadcrumb"><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Master Data</li><li class="breadcrumb-item active">Sumber Dana</li></ol></nav>
    <header class="category-page-header"><div><h1>Sumber Dana</h1><p>Kelola sumber pembiayaan pengadaan aset sekolah.</p></div>@if(auth()->user()->isAdmin())<button class="btn category-primary-btn" data-bs-toggle="modal" data-bs-target="#createSourceModal"><i data-lucide="plus"></i>Tambah Sumber Dana</button>@endif</header>
    <section class="category-mini-summary location-summary"><div><span>Total Sumber Dana</span><strong>{{ $totalSources }}</strong></div><i></i><div><span>Sumber Dana Aktif</span><strong>{{ $activeSources }}</strong></div><i></i><div><span>Total Aset</span><strong>{{ $totalAssets }}</strong></div></section>
    <section class="category-panel">
        <form method="GET" action="{{ route('funding-sources.index') }}" class="category-toolbar"><div class="category-search"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode sumber dana..." aria-label="Cari sumber dana"></div><select name="status" class="category-status-filter" onchange="this.form.submit()"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select><button class="btn category-search-btn">Cari</button>@if(request()->filled('search') || request()->filled('status'))<a href="{{ route('funding-sources.index') }}" class="category-reset"><i data-lucide="rotate-ccw"></i>Reset</a>@endif</form>
        @if($sources->isEmpty())
            <div class="category-empty"><div><i data-lucide="landmark"></i></div><h2>Belum ada sumber dana</h2><p>Tambahkan sumber dana untuk mencatat asal pembiayaan aset sekolah.</p>@if(auth()->user()->isAdmin())<button class="btn category-primary-btn" data-bs-toggle="modal" data-bs-target="#createSourceModal"><i data-lucide="plus"></i>Tambah Sumber Dana</button>@endif</div>
        @else
            <div class="category-table-wrap"><table class="category-table funding-table"><thead><tr><th>No</th><th>Kode</th><th>Nama Sumber Dana</th><th>Deskripsi</th><th>Jumlah Aset</th><th>Status</th>@if(auth()->user()->isAdmin())<th class="text-end">Aksi</th>@endif</tr></thead><tbody>
            @foreach($sources as $source)
                @php
                    $sourcePayload = json_encode($source->only(['id', 'code', 'name', 'description', 'is_active']));
                @endphp
                <tr><td class="row-number">{{ str_pad($sources->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT) }}</td><td><span class="category-code">{{ $source->code }}</span></td><td><b class="category-name">{{ $source->name }}</b></td><td><span class="category-description" title="{{ $source->description }}">{{ $source->description ?: 'Tidak ada deskripsi' }}</span></td><td><span class="asset-count">{{ (int) $source->assets_count }} Aset</span></td><td><span class="category-status {{ $source->is_active ? 'active' : 'inactive' }}">{{ $source->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                @if(auth()->user()->isAdmin())<td class="text-end"><div class="dropdown"><button class="category-action" data-bs-toggle="dropdown" aria-label="Aksi untuk {{ $source->name }}"><i data-lucide="ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end category-action-menu"><li><button class="dropdown-item edit-source" type="button" data-bs-toggle="modal" data-bs-target="#editSourceModal" data-update-url="{{ route('funding-sources.update', $source) }}" data-source="{{ $sourcePayload }}"><i data-lucide="pencil"></i>Edit</button></li><li><form method="POST" action="{{ route('funding-sources.toggle', $source) }}">@csrf @method('PATCH')<button class="dropdown-item"><i data-lucide="power"></i>{{ $source->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></li><li><hr class="dropdown-divider"></li><li><button class="dropdown-item delete-source text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteSourceModal" data-delete-url="{{ route('funding-sources.destroy', $source) }}" data-source-name="{{ $source->name }}"><i data-lucide="trash-2"></i>Hapus</button></li></ul></div></td>@endif</tr>
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
                    <span>Menampilkan {{ $sources->firstItem() }}–{{ $sources->lastItem() }} dari {{ $sources->total() }} sumber dana</span>
                </div>
                {{ $sources->links() }}
            </div>
        @endif
    </section>
</div>

@if(auth()->user()->isAdmin())
<div class="modal fade category-modal" id="createSourceModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h2>Tambah Sumber Dana</h2><p>Tambahkan sumber pembiayaan aset sekolah.</p></div><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('funding-sources.store') }}">@csrf<input type="hidden" name="_form_mode" value="create"><div class="modal-body">@include('funding-sources.partials.form-fields', ['prefix'=>'create','showErrors'=>old('_form_mode','create') === 'create'])</div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button class="btn category-primary-btn">Simpan Sumber Dana</button></div></form></div></div></div>
<div class="modal fade category-modal" id="editSourceModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h2>Edit Sumber Dana</h2><p>Perbarui informasi sumber dana yang dipilih.</p></div><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="editSourceForm" action="{{ old('_source_id') ? route('funding-sources.update', old('_source_id')) : '#' }}">@csrf @method('PUT')<input type="hidden" name="_form_mode" value="edit"><input type="hidden" name="_source_id" value="{{ old('_source_id') }}"><div class="modal-body">@include('funding-sources.partials.form-fields', ['prefix'=>'edit','showErrors'=>old('_form_mode') === 'edit'])</div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button class="btn category-primary-btn">Simpan Perubahan</button></div></form></div></div></div>
<div class="modal fade category-modal delete-modal" id="deleteSourceModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><form method="POST" id="deleteSourceForm">@csrf @method('DELETE')<div class="modal-body"><div class="delete-icon"><i data-lucide="trash-2"></i></div><h2>Hapus sumber dana?</h2><p><b id="deleteSourceName"></b> akan dihapus dari daftar sumber dana.</p></div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button class="btn category-delete-btn">Hapus Sumber Dana</button></div></form></div></div></div>
@endif
@php
    $sourceFormState = ['mode' => old('_form_mode'), 'sourceId' => old('_source_id')];
@endphp
@push('scripts')<script>window.sourceFormState = @json($sourceFormState);</script>@vite('resources/js/funding-sources.js')@endpush
</x-app-layout>
