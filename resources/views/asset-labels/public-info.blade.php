<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Informasi {{ $asset->asset_code }} - {{ $schoolSetting->display_name }}</title>@vite(['resources/css/app.css','resources/css/school-identity.css'])</head>
<body class="public-asset-page"><main class="public-asset-card">
    <div class="public-school-mark {{ $schoolSetting->logo_url ? 'has-logo' : '' }}">@if($schoolSetting->logo_url)<img class="public-school-logo" src="{{ $schoolSetting->logo_url }}" alt="Logo {{ $schoolSetting->display_name }}">@else<i data-lucide="school"></i>@endif</div>
    <header><span>{{ $schoolSetting->display_name }}</span><h1>Informasi Barang Inventaris</h1><p>Informasi publik aset milik sekolah.</p></header>
    <div class="public-asset-code">{{ $asset->asset_code }}</div><h2>{{ $asset->name }}</h2><div class="public-statuses"><span class="asset-badge condition-{{ $asset->condition }}">{{ $asset->condition_label }}</span><span class="asset-badge status-{{ $asset->status }}">{{ $asset->status === 'dihapus' ? 'Tidak Aktif / Dihapus dari Inventaris' : $asset->status_label }}</span></div>
    <dl><div><dt>Kategori</dt><dd>{{ $asset->category->name }}</dd></div><div><dt>Lokasi / Ruangan</dt><dd>{{ $asset->location->name }}</dd></div><div><dt>Tahun Pengadaan</dt><dd>{{ $asset->acquisition_year }}</dd></div></dl>
    <footer>Data terverifikasi dari Sistem Inventaris &amp; Aset {{ $schoolSetting->display_name }}.</footer>
</main>@vite('resources/js/app.js')</body></html>
