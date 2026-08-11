<x-app-layout title="Kategori Aset">
    <div class="category-page">
        <nav aria-label="breadcrumb"><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Master Data</li><li class="breadcrumb-item active">Kategori Aset</li></ol></nav>

        <header class="category-page-header">
            <div><h1>Kategori Aset</h1><p>Kelola kelompok atau jenis barang inventaris sekolah.</p></div>
            @if(auth()->user()->isAdmin())<button class="btn category-primary-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal"><i data-lucide="plus"></i>Tambah Kategori</button>@endif
        </header>

        <section class="category-mini-summary" aria-label="Ringkasan kategori">
            <div><span>Total Kategori</span><strong>{{ $totalCategories }}</strong></div><i></i><div><span>Kategori Aktif</span><strong>{{ $activeCategories }}</strong></div>
        </section>

        <section class="category-panel">
            <form method="GET" action="{{ route('asset-categories.index') }}" class="category-toolbar">
                <div class="category-search"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode kategori..." aria-label="Cari kategori"></div>
                <select name="status" class="category-status-filter" aria-label="Filter status" onchange="this.form.submit()"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select>
                <button class="btn category-search-btn" type="submit">Cari</button>
                @if(request()->filled('search') || request()->filled('status'))<a href="{{ route('asset-categories.index') }}" class="category-reset"><i data-lucide="rotate-ccw"></i>Reset</a>@endif
            </form>

            @if($categories->isEmpty())
                <div class="category-empty"><div><i data-lucide="tags"></i></div><h2>Belum ada kategori aset</h2><p>Tambahkan kategori untuk mulai mengelompokkan barang inventaris.</p>@if(auth()->user()->isAdmin())<button class="btn category-primary-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal"><i data-lucide="plus"></i>Tambah Kategori</button>@endif</div>
            @else
                <div class="category-table-wrap"><table class="category-table"><thead><tr><th>No</th><th>Kode</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Aset</th><th>Status</th>@if(auth()->user()->isAdmin())<th class="text-end">Aksi</th>@endif</tr></thead><tbody>
                    @foreach($categories as $category)
                        @php
                            $categoryPayload = json_encode($category->only(['id', 'name', 'code', 'description', 'is_active']));
                        @endphp
                        <tr><td class="row-number">{{ str_pad($categories->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT) }}</td><td><span class="category-code">{{ $category->code ?: '—' }}</span></td><td><b class="category-name">{{ $category->name }}</b></td><td><span class="category-description" title="{{ $category->description }}">{{ $category->description ?: 'Tidak ada deskripsi' }}</span></td><td><span class="asset-count">{{ (int) $category->assets_count }} Aset</span></td><td><span class="category-status {{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        @if(auth()->user()->isAdmin())<td class="text-end"><div class="dropdown"><button class="category-action" data-bs-toggle="dropdown" aria-label="Aksi untuk {{ $category->name }}"><i data-lucide="ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end category-action-menu">
                            <li><button class="dropdown-item edit-category" type="button" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-update-url="{{ route('asset-categories.update', $category) }}" data-category="{{ $categoryPayload }}"><i data-lucide="pencil"></i>Edit</button></li>
                            <li><form method="POST" action="{{ route('asset-categories.toggle', $category) }}">@csrf @method('PATCH')<button class="dropdown-item" type="submit"><i data-lucide="power"></i>{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></li>
                            <li><hr class="dropdown-divider"></li><li><button class="dropdown-item delete-category text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal" data-delete-url="{{ route('asset-categories.destroy', $category) }}" data-category-name="{{ $category->name }}"><i data-lucide="trash-2"></i>Hapus</button></li>
                        </ul></div></td>@endif</tr>
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
                        <span>Menampilkan {{ $categories->firstItem() }}–{{ $categories->lastItem() }} dari {{ $categories->total() }} kategori</span>
                    </div>
                    {{ $categories->links() }}
                </div>
            @endif
        </section>
    </div>

    @if(auth()->user()->isAdmin())
    <div class="modal fade category-modal" id="createCategoryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h2>Tambah Kategori Aset</h2><p>Tambahkan kelompok baru untuk inventaris sekolah.</p></div><button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><form method="POST" action="{{ route('asset-categories.store') }}">@csrf<input type="hidden" name="_form_mode" value="create"><div class="modal-body">
        @include('asset-categories.partials.form-fields', ['prefix' => 'create', 'category' => null, 'showErrors' => old('_form_mode', 'create') === 'create'])
    </div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn category-primary-btn">Simpan Kategori</button></div></form></div></div></div>

    <div class="modal fade category-modal" id="editCategoryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h2>Edit Kategori Aset</h2><p>Perbarui informasi kategori yang dipilih.</p></div><button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><form method="POST" id="editCategoryForm" action="{{ old('_category_id') ? route('asset-categories.update', old('_category_id')) : '#' }}">@csrf @method('PUT')<input type="hidden" name="_form_mode" value="edit"><input type="hidden" name="_category_id" id="editCategoryId" value="{{ old('_category_id') }}"><div class="modal-body">
        @include('asset-categories.partials.form-fields', ['prefix' => 'edit', 'category' => null, 'showErrors' => old('_form_mode') === 'edit'])
    </div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn category-primary-btn">Simpan Perubahan</button></div></form></div></div></div>

    <div class="modal fade category-modal delete-modal" id="deleteCategoryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><form method="POST" id="deleteCategoryForm">@csrf @method('DELETE')<div class="modal-body"><div class="delete-icon"><i data-lucide="trash-2"></i></div><h2>Hapus kategori?</h2><p>Kategori <b id="deleteCategoryName"></b> akan dihapus. Tindakan ini tidak dapat dibatalkan.</p></div><div class="modal-footer"><button type="button" class="btn category-cancel-btn" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn category-delete-btn">Hapus Kategori</button></div></form></div></div></div>
    @endif

    @php
        $categoryFormState = [
            'mode' => old('_form_mode'),
            'categoryId' => old('_category_id'),
            'values' => ['name' => old('name'), 'code' => old('code'), 'description' => old('description'), 'is_active' => old('is_active')],
        ];
    @endphp
    @push('scripts')<script>window.categoryFormState = @json($categoryFormState);</script>@vite('resources/js/asset-categories.js')@endpush
</x-app-layout>
