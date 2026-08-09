<x-app-layout :title="$page['title']">
    <x-breadcrumb :current="$page['title']" />
    <header class="page-header"><h1>{{ $page['title'] }}</h1><p>{{ $page['description'] }}</p></header>
    <section class="content-card empty-state"><div class="empty-state-icon"><i data-lucide="{{ $page['icon'] }}"></i></div><h2>Modul belum tersedia</h2><p class="mb-0">Halaman ini telah disiapkan dan akan dikembangkan pada tahap selanjutnya.</p></section>
</x-app-layout>
