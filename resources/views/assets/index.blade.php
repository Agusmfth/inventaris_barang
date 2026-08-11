<x-app-layout title="Data Aset">
<div class="category-page asset-page">
    <nav aria-label="breadcrumb"><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Inventaris</li><li class="breadcrumb-item active">Data Aset</li></ol></nav>
    <header class="category-page-header"><div><h1>Data Aset</h1><p>Kelola seluruh barang inventaris dan aset sekolah.</p></div>@if(auth()->user()->isAdmin())<a href="{{ route('assets.create') }}" class="btn category-primary-btn"><i data-lucide="plus"></i>Tambah Aset</a>@endif</header>
    <section class="category-mini-summary asset-mini-summary"><div><span>Total Aset</span><strong>{{ number_format($summary['total'],0,',','.') }}</strong></div><i></i><div><span>Kondisi Baik</span><strong>{{ number_format($summary['good'],0,',','.') }}</strong></div><i></i><div><span>Rusak Ringan</span><strong>{{ number_format($summary['light'],0,',','.') }}</strong></div><i></i><div><span>Rusak Berat</span><strong>{{ number_format($summary['heavy'],0,',','.') }}</strong></div></section>
    <section class="category-panel">
        <form method="GET" action="{{ route('assets.index') }}" class="asset-toolbar">
            <div class="category-search asset-search"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari kode, nama aset, merk, atau nomor seri..."></div>
            <div class="asset-filters"><select name="category"><option value="">Semua Kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select><select name="location"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(request('location') == $location->id)>{{ $location->name }}</option>@endforeach</select><select name="condition"><option value="">Semua Kondisi</option><option value="baik" @selected(request('condition')==='baik')>Baik</option><option value="rusak_ringan" @selected(request('condition')==='rusak_ringan')>Rusak Ringan</option><option value="rusak_berat" @selected(request('condition')==='rusak_berat')>Rusak Berat</option></select><select name="status"><option value="">Semua Status</option><option value="tersedia" @selected(request('status')==='tersedia')>Tersedia</option><option value="dipinjam" @selected(request('status')==='dipinjam')>Dipinjam</option><option value="perawatan" @selected(request('status')==='perawatan')>Perawatan</option><option value="dihapus" @selected(request('status')==='dihapus')>Dihapus</option></select><select name="year"><option value="">Semua Tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected(request('year')==$year)>{{ $year }}</option>@endforeach</select></div>
            <div class="asset-filter-actions"><button class="btn category-search-btn">Filter</button>@if(collect(request()->only(['search','category','location','condition','status','year']))->filter()->isNotEmpty())<a href="{{ route('assets.index') }}" class="category-reset"><i data-lucide="rotate-ccw"></i>Reset</a>@endif</div>
        </form>
        @if($assets->isEmpty())
            <div class="category-empty"><div><i data-lucide="package"></i></div><h2>Belum ada data aset</h2><p>Tambahkan aset untuk mulai mencatat inventaris sekolah.</p>@if(auth()->user()->isAdmin())<a href="{{ route('assets.create') }}" class="btn category-primary-btn"><i data-lucide="plus"></i>Tambah Aset</a>@endif</div>
        @else
            <div class="category-table-wrap"><table class="category-table asset-table"><thead><tr><th>Kode</th><th>Nama Aset</th><th>Kategori</th><th>Lokasi</th><th>Tahun</th><th>Kondisi</th><th>Status</th><th>Nilai</th><th class="text-end">Aksi</th></tr></thead><tbody>
                @foreach($assets as $asset)<tr><td><b class="asset-code">{{ $asset->asset_code }}</b></td><td><div class="asset-name-cell"><b>{{ $asset->name }}</b>@if($asset->brand || $asset->model)<small>{{ collect([$asset->brand,$asset->model])->filter()->join(' • ') }}</small>@endif</div></td><td>{{ $asset->category->name }}</td><td>{{ $asset->location->name }}</td><td>{{ $asset->acquisition_year }}</td><td><span class="asset-badge condition-{{ $asset->condition }}">{{ $asset->condition_label }}</span></td><td><span class="asset-badge status-{{ $asset->status }}">{{ $asset->status_label }}</span></td><td><span class="asset-value">Rp {{ number_format((float)$asset->acquisition_price * $asset->quantity,0,',','.') }}</span>@if($asset->quantity > 1)<small class="asset-quantity">{{ $asset->quantity }} barang</small>@endif</td><td class="text-end"><div class="dropdown"><button class="category-action" data-bs-toggle="dropdown"><i data-lucide="ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end category-action-menu"><li><a class="dropdown-item" href="{{ route('assets.show',$asset) }}"><i data-lucide="eye"></i>Lihat Detail</a></li>@if(auth()->user()->isAdmin())<li><a class="dropdown-item" href="{{ route('assets.edit',$asset) }}"><i data-lucide="pencil"></i>Edit</a></li><li><a class="dropdown-item" href="{{ route('asset-labels.single',$asset) }}"><i data-lucide="printer"></i>Cetak Label</a></li>@endif</ul></div></td></tr>@endforeach
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
                    <span>Menampilkan {{ $assets->firstItem() }}–{{ $assets->lastItem() }} dari {{ $assets->total() }} data aset</span>
                </div>
                {{ $assets->links() }}
            </div>
        @endif
    </section>
</div>
</x-app-layout>
