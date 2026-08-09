<x-app-layout title="Detail Mutasi Aset">
<div class="category-page mutation-detail-page">
    <nav aria-label="breadcrumb"><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('asset-mutations.index') }}">Mutasi Aset</a></li><li class="breadcrumb-item active">Detail Mutasi</li></ol></nav>
    <header class="category-page-header"><div><h1>Detail Mutasi Aset</h1><p>Informasi perpindahan dan pencatatan aset.</p></div><a href="{{ route('asset-mutations.index') }}" class="btn category-cancel-btn"><i data-lucide="arrow-left"></i>Kembali</a></header>
    <section class="asset-detail-card mutation-detail-card">
        <div class="mutation-detail-heading"><span><i data-lucide="arrow-left-right"></i></span><div><small>ASET YANG DIMUTASI</small><h2>{{ $mutation->asset->name }}</h2><p>{{ $mutation->asset->asset_code }}</p></div><time>{{ $mutation->mutation_date->locale('id')->translatedFormat('d F Y') }}</time></div>
        <div class="mutation-timeline"><div><small>Lokasi Asal</small><strong>{{ $mutation->fromLocation->name }}</strong></div><span><i data-lucide="arrow-down"></i><b>Dipindahkan</b></span><div class="destination"><small>Lokasi Tujuan</small><strong>{{ $mutation->toLocation->name }}</strong></div></div>
        <div class="mutation-detail-section"><h3>Informasi Mutasi</h3><dl class="mutation-detail-info"><div><dt>Alasan</dt><dd>{{ $mutation->reason ?: '—' }}</dd></div><div><dt>Catatan</dt><dd>{{ $mutation->notes ?: '—' }}</dd></div><div><dt>Diproses oleh</dt><dd>{{ $mutation->creator?->name ?: 'Pengguna tidak tersedia' }}</dd></div><div><dt>Tanggal pencatatan</dt><dd>{{ $mutation->created_at->locale('id')->translatedFormat('d F Y, H:i') }}</dd></div></dl></div>
    </section>
</div>
</x-app-layout>
