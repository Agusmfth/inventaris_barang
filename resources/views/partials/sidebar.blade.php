@php($navigation = config('navigation'))
<aside class="app-sidebar" id="appSidebar" data-turbo-permanent>
    <div class="sidebar-brand">
        <div class="brand-mark {{ $schoolSetting->logo_url ? 'has-school-logo' : '' }}">@if($schoolSetting->logo_url)<img src="{{ $schoolSetting->logo_url }}" alt="Logo {{ $schoolSetting->display_name }}">@else<i data-lucide="school"></i>@endif</div>
        <div class="brand-copy"><strong title="{{ $schoolSetting->display_name }}">{{ $schoolSetting->display_name }}</strong><small>Inventaris & Aset</small></div>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Tutup navigasi"><i data-lucide="x"></i></button>
    </div>
    <nav class="sidebar-nav" aria-label="Navigasi utama">
        <a href="{{ route('dashboard') }}" title="Dashboard" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
        @foreach($navigation['sections'] as $section => $slugs)
            @php($visiblePages = collect($slugs)->filter(fn($slug) => in_array(auth()->user()->role, $navigation['pages'][$slug]['roles'], true)))
            @if($visiblePages->isNotEmpty())
                <div class="nav-section-title">{{ $section }}</div>
                @foreach($visiblePages as $slug)
                    @php($item = $navigation['pages'][$slug])
                    <a href="{{ isset($item['route']) ? route($item['route']) : route('placeholder', $slug) }}" title="{{ $item['title'] }}" class="sidebar-link {{ (isset($item['route']) && request()->routeIs(Str::beforeLast($item['route'], '.').'.*')) || request()->is('halaman/'.$slug) ? 'active' : '' }}"><i data-lucide="{{ $item['icon'] }}"></i><span>{{ $item['title'] }}</span></a>
                @endforeach
            @endif
        @endforeach
    </nav>
    <div class="sidebar-footer"><form method="POST" action="{{ route('logout') }}">@csrf<button class="sidebar-link" type="submit"><i data-lucide="log-out"></i><span>Keluar</span></button></form></div>
</aside>
