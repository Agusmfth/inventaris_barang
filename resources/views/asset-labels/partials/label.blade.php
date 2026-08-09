<article class="inventory-label inventory-card-label">
    <header class="label-card-header">
        <div class="label-school-brand">
            @if($schoolSetting->logo_url)
                <img src="{{ $schoolSetting->logo_url }}" alt="Logo {{ $schoolSetting->display_name }}">
            @else
                <span class="label-school-emblem">SD</span>
            @endif
            <div><strong>{{ $schoolSetting->display_name }}</strong><small>SISTEM INVENTARIS &amp; ASET</small></div>
        </div>
        <div class="label-inventory-mark"><span>◆</span><b>{{ $schoolSetting->label_mark }}</b></div>
    </header>
    <div class="label-card-banner">{{ $schoolSetting->label_title }}</div>
    <div class="label-card-content">
        <div class="label-card-information">
            <div class="label-asset-title"><span class="label-asset-symbol">▣</span><div><small>NAMA ASET</small><h2>{{ $asset->name }}</h2></div></div>
            <dl>
                <div><dt>Kode Inventaris</dt><dd>{{ $asset->asset_code }}</dd></div>
                <div><dt>Kategori</dt><dd>{{ $asset->category->name }}</dd></div>
                <div><dt>Lokasi / Ruangan</dt><dd>{{ $asset->location->name }}</dd></div>
                <div><dt>Tahun Pengadaan</dt><dd>{{ $asset->acquisition_year }}</dd></div>
                <div class="label-secondary-row"><dt>Kondisi</dt><dd><span class="label-card-badge condition">{{ $asset->condition_label }}</span></dd></div>
                <div class="label-secondary-row"><dt>Status</dt><dd><span class="label-card-badge status">{{ $asset->status_label }}</span></dd></div>
            </dl>
        </div>
        <aside class="label-card-scan">
            <strong>SCAN UNTUK INFORMASI</strong>
            <img src="{{ $qrDataUri }}" alt="QR informasi {{ $asset->asset_code }}">
            <small>Aset Milik<b>{{ $schoolSetting->display_name }}</b></small>
        </aside>
    </div>
    <footer><span>♢</span> {{ $schoolSetting->label_footer }} @if($asset->quantity > 1)<b>UNIT {{ str_pad($unit,2,'0',STR_PAD_LEFT) }}/{{ str_pad($asset->quantity,2,'0',STR_PAD_LEFT) }}</b>@endif</footer>
</article>
