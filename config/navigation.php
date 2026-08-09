<?php

use App\Models\User;

$all = [User::ROLE_ADMIN, User::ROLE_KEPALA_SEKOLAH];
$admin = [User::ROLE_ADMIN];

return [
    'pages' => [
        'kategori-aset' => ['title' => 'Kategori Aset', 'description' => 'Kelola klasifikasi aset sekolah.', 'icon' => 'shapes', 'roles' => $admin, 'route' => 'asset-categories.index'],
        'lokasi-ruangan' => ['title' => 'Lokasi / Ruangan', 'description' => 'Kelola lokasi penyimpanan dan ruangan.', 'icon' => 'map-pin', 'roles' => $admin, 'route' => 'asset-locations.index'],
        'sumber-dana' => ['title' => 'Sumber Dana', 'description' => 'Kelola sumber perolehan aset.', 'icon' => 'landmark', 'roles' => $admin, 'route' => 'funding-sources.index'],
        'data-aset' => ['title' => 'Data Aset', 'description' => 'Daftar seluruh aset dan inventaris sekolah.', 'icon' => 'package', 'roles' => $all, 'route' => 'assets.index'],
        'cetak-label' => ['title' => 'Cetak Label', 'description' => 'Siapkan dan cetak label identitas aset.', 'icon' => 'qr-code', 'roles' => $admin, 'route' => 'asset-labels.index'],
        'qr-code-aset' => ['title' => 'QR Code Aset', 'description' => 'Kelola QR Code identifikasi aset.', 'icon' => 'scan-qr-code', 'roles' => $admin, 'route' => 'asset-qr-codes.index'],
        'mutasi-aset' => ['title' => 'Mutasi Aset', 'description' => 'Catat perpindahan aset antar lokasi.', 'icon' => 'arrow-left-right', 'roles' => $all, 'route' => 'asset-mutations.index'],
        'peminjaman' => ['title' => 'Peminjaman', 'description' => 'Kelola peminjaman dan pengembalian aset.', 'icon' => 'clipboard-list', 'roles' => $all, 'route' => 'asset-loans.index'],
        'perawatan' => ['title' => 'Perawatan / Kerusakan', 'description' => 'Pantau perawatan dan kondisi kerusakan.', 'icon' => 'wrench', 'roles' => $all, 'route' => 'asset-maintenances.index'],
        'penghapusan' => ['title' => 'Penghapusan Aset', 'description' => 'Kelola pengajuan penghapusan aset.', 'icon' => 'trash-2', 'roles' => $all, 'route' => 'asset-disposals.index'],
        'laporan-aset' => ['title' => 'Laporan Aset', 'description' => 'Lihat dan ekspor laporan inventaris.', 'icon' => 'file-chart-column', 'roles' => $all, 'route' => 'asset-reports.index'],
        'pengguna' => ['title' => 'Pengguna', 'description' => 'Kelola akun dan hak akses pengguna.', 'icon' => 'users', 'roles' => $admin, 'route' => 'users.index'],
        'identitas-sekolah' => ['title' => 'Identitas Sekolah', 'description' => 'Konfigurasi identitas dan logo sekolah.', 'icon' => 'school', 'roles' => $admin, 'route' => 'school-settings.edit'],
    ],
    'sections' => [
        'MASTER DATA' => ['kategori-aset', 'lokasi-ruangan', 'sumber-dana'],
        'INVENTARIS' => ['data-aset', 'cetak-label', 'qr-code-aset', 'mutasi-aset', 'peminjaman', 'perawatan', 'penghapusan'],
        'LAPORAN' => ['laporan-aset'],
        'PENGATURAN' => ['pengguna', 'identitas-sekolah'],
    ],
];
