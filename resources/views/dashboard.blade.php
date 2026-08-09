<x-app-layout title="Dashboard">
    <div class="dashboard-page">
        <header class="dashboard-header">
            <div><h1>Dashboard</h1><p>Ringkasan data inventaris dan aset sekolah</p></div>
            <div class="dashboard-date"><i data-lucide="calendar-days"></i><span>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></div>
        </header>

        <section class="summary-grid" aria-label="Ringkasan inventaris">
            @foreach($summary as $item)
                <article class="summary-card tone-{{ $item['tone'] }}">
                    <div class="summary-icon"><i data-lucide="{{ $item['icon'] }}"></i></div>
                    <div class="summary-content">
                        <span class="summary-label">{{ $item['label'] }}</span>
                        <div class="summary-value"><strong>{{ $item['value'] }}</strong><span>{{ $item['unit'] }}</span></div>
                        <p>{{ $item['description'] }}</p>
                        @if($item['detail'])<b class="summary-detail">{{ $item['detail'] }}</b>@endif
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-grid charts-row">
            <article class="dashboard-card chart-card">
                <div class="card-heading"><div><h2>Aset Berdasarkan Kategori</h2><p>Distribusi aset berdasarkan jenis kategori</p></div></div>
                <div class="category-chart-layout">
                    <div class="donut-container"><canvas id="categoryChart" aria-label="Grafik kategori aset"></canvas></div>
                    <div class="chart-legend">
                        @foreach($charts['categories']['labels'] as $index => $label)
                            <div class="legend-row"><span class="legend-label"><i style="background:{{ $charts['categories']['colors'][$index] }}"></i>{{ $label }}</span><span><b>{{ $charts['categories']['values'][$index] }}</b><small>{{ $charts['total'] > 0 ? number_format(($charts['categories']['values'][$index] / $charts['total']) * 100, 1, ',', '.') : '0,0' }}%</small></span></div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="dashboard-card chart-card">
                <div class="card-heading"><div><h2>Aset Berdasarkan Kondisi</h2><p>Perbandingan kondisi seluruh aset</p></div><span class="period-label">Tahun ini</span></div>
                <div class="bar-container"><canvas id="conditionChart" aria-label="Grafik kondisi aset"></canvas></div>
            </article>
        </section>

        <section class="dashboard-grid bottom-row">
            <article class="dashboard-card table-card">
                <div class="card-heading"><div><h2>Aset Terbaru</h2><p>Data aset yang terakhir ditambahkan</p></div><a class="dashboard-nav-link" href="{{ route('assets.index') }}"><span>Lihat semua</span><i data-lucide="arrow-right"></i></a></div>
                <div class="dashboard-table-wrap"><table class="dashboard-table"><thead><tr><th>Kode Aset</th><th>Nama Aset</th><th>Kategori</th><th>Lokasi</th><th>Kondisi</th><th>Tanggal</th></tr></thead><tbody>
                    @foreach($latestAssets as $asset)
                        <tr><td><b>{{ $asset['code'] }}</b></td><td>{{ $asset['name'] }}</td><td>{{ $asset['category'] }}</td><td>{{ $asset['location'] }}</td><td><span class="condition-badge {{ $asset['condition_key'] === 'baik' ? 'good' : ($asset['condition_key'] === 'rusak_ringan' ? 'light' : 'heavy') }}">{{ $asset['condition'] }}</span></td><td>{{ $asset['date'] }}</td></tr>
                    @endforeach
                </tbody></table></div>
            </article>

            <article class="dashboard-card activity-card">
                <div class="card-heading"><div><h2>Aktivitas Terbaru</h2><p>Aktivitas inventaris terkini</p></div><span class="period-label">5 terbaru</span></div>
                <div class="activity-list">
                    @foreach($activities as $activity)
                        <div class="activity-item"><div class="activity-mark activity-{{ $activity['tone'] }}"><i data-lucide="{{ $activity['icon'] }}"></i></div><div class="activity-copy"><b>{{ $activity['type'] }}</b><span>{{ $activity['description'] }}</span></div><time>{{ $activity['time'] }}</time></div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
    <script type="application/json" id="dashboardChartData">@json($charts)</script>
    <div class="dashboard-action-feedback" id="dashboardActionFeedback" aria-live="polite" aria-hidden="true"><div><span></span><b>Membuka halaman...</b></div></div>
    @push('scripts')@vite('resources/js/dashboard.js')@endpush
</x-app-layout>
