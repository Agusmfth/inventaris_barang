<x-app-layout title="Scan QR Aset">
@push('scripts')
    @vite('resources/js/asset-scanner.js')
@endpush
<div class="category-page scanner-page" id="assetScanner" data-resolve-url="{{ route('asset-scanner.resolve') }}">
    <nav aria-label="breadcrumb"><ol class="breadcrumb category-breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Scan QR Aset</li></ol></nav>
    <header class="category-page-header scanner-heading"><div><h1>Scan QR Aset</h1><p>Arahkan kamera ke QR Code yang terdapat pada label barang inventaris.</p></div></header>
    <div class="scanner-layout">
        <section class="scanner-card">
            <div class="scanner-card-heading"><div class="scanner-title-icon"><i data-lucide="scan-line"></i></div><div><h2>Pemindai QR Code</h2><p>Gunakan kamera belakang untuk hasil pemindaian terbaik.</p></div><span class="scanner-secure"><i data-lucide="shield-check"></i>Aman</span></div>
            <div class="scanner-viewport" id="scannerViewport"><div id="qrReader"></div><div class="scanner-placeholder" id="scannerPlaceholder"><i data-lucide="camera"></i><strong>Kamera belum aktif</strong><span>Tekan Mulai Kamera untuk memindai label aset.</span></div><div class="scanner-frame" aria-hidden="true"><i></i><i></i><i></i><i></i></div></div>
            <p class="scanner-guidance"><i data-lucide="focus"></i>Posisikan QR Code di dalam area pemindaian.</p>
            <div class="scanner-actions"><button type="button" class="btn category-primary-btn" id="startScanner"><i data-lucide="camera"></i>Mulai Kamera</button><button type="button" class="btn category-cancel-btn" id="switchCamera" hidden><i data-lucide="switch-camera"></i>Ganti Kamera</button><button type="button" class="btn scanner-stop" id="stopScanner" hidden><i data-lucide="camera-off"></i>Matikan Kamera</button></div>
            <div class="scanner-result" id="scannerResult" role="status" aria-live="polite" hidden></div>
            <p class="scanner-https-note"><i data-lucide="lock-keyhole"></i>Kamera memerlukan HTTPS pada server online. Localhost tetap dapat digunakan saat pengembangan.</p>
        </section>
        <aside class="scanner-manual-card">
            <div class="scanner-card-heading"><div class="scanner-title-icon secondary"><i data-lucide="keyboard"></i></div><div><h2>Masukkan Kode Aset</h2><p>Gunakan jika kamera tidak tersedia atau QR rusak.</p></div></div>
            <form id="manualAssetForm" novalidate><label for="manualAssetCode">Kode aset</label><div class="manual-code-field"><i data-lucide="package"></i><input id="manualAssetCode" class="form-control" placeholder="AST-2026-0001" autocomplete="off" maxlength="30"><button class="btn category-primary-btn" type="submit"><i data-lucide="search"></i>Cari Aset</button></div><small>Masukkan kode inventaris yang tertera pada label.</small></form>
            <div class="scanner-help"><strong><i data-lucide="info"></i>Petunjuk singkat</strong><ol><li>Izinkan akses kamera pada browser.</li><li>Arahkan kamera ke QR pada label.</li><li>Detail aset akan terbuka otomatis.</li></ol></div>
        </aside>
    </div>
</div>
</x-app-layout>
