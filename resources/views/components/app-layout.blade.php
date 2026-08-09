<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B2F35">
    <script>document.documentElement.classList.add('app-booting')</script>
    <title>{{ isset($title) ? $title.' - ' : '' }}{{ $schoolSetting->display_name }} · Sistem Inventaris & Aset</title>
    @vite(['resources/css/app.css', 'resources/css/school-identity.css', 'resources/css/notifications.css', 'resources/css/dashboard-polish.css', 'resources/css/asset-disposals.css', 'resources/css/asset-reports.css', 'resources/css/asset-labels.css', 'resources/css/asset-qr-codes.css', 'resources/css/asset-loans.css', 'resources/css/asset-mutations.css', 'resources/css/asset-maintenances.css', 'resources/css/asset-scanner.css', 'resources/css/users.css', 'resources/css/school-settings.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <div class="sidebar-backdrop" id="sidebarBackdrop" data-turbo-permanent></div>
    <div class="app-shell">
        @include('partials.topbar')
        <main class="app-content">
            {{ $slot }}
        </main>
    </div>
    @include('partials.toast')
    @include('partials.modal')
    @stack('scripts')
</body>
</html>
