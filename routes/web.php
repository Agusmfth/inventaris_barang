<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetLocationController;
use App\Http\Controllers\FundingSourceController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLabelController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\AssetMutationController;
use App\Http\Controllers\AssetLoanController;
use App\Http\Controllers\AssetMaintenanceController;
use App\Http\Controllers\AssetDisposalController;
use App\Http\Controllers\AssetReportController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\AssetQrCodeController;
use App\Http\Controllers\AssetScannerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::get('/aset/{asset:asset_code}/info', [AssetLabelController::class, 'publicInfo'])->name('assets.public-info');
Route::get('/identitas-sekolah/logo', [SchoolSettingController::class, 'logo'])->name('school-settings.logo');
Route::get('/media/{path}', PublicMediaController::class)->where('path', '(assets|school)/[A-Za-z0-9._/-]+')->name('media.show');

Route::middleware(['auth','active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{notification}/buka', [NotificationController::class, 'open'])->name('notifications.open');
    Route::patch('/notifikasi/{notification}/baca', [NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifikasi/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/scan-aset', [AssetScannerController::class, 'index'])->name('asset-scanner.index');
    Route::post('/scan-aset/resolve', [AssetScannerController::class, 'resolve'])->name('asset-scanner.resolve');

    Route::get('/kategori-aset', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
    Route::get('/lokasi-ruangan', [AssetLocationController::class, 'index'])->name('asset-locations.index');
    Route::get('/sumber-dana', [FundingSourceController::class, 'index'])->name('funding-sources.index');
    Route::get('/data-aset', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/mutasi-aset', [AssetMutationController::class, 'index'])->name('asset-mutations.index');
    Route::get('/mutasi-aset/{mutation}', [AssetMutationController::class, 'show'])->name('asset-mutations.show');
    Route::get('/peminjaman', [AssetLoanController::class, 'index'])->name('asset-loans.index');
    Route::get('/peminjaman/{loan}', [AssetLoanController::class, 'show'])->name('asset-loans.show');
    Route::get('/perawatan', [AssetMaintenanceController::class, 'index'])->name('asset-maintenances.index');
    Route::get('/perawatan/{maintenance}', [AssetMaintenanceController::class, 'show'])->name('asset-maintenances.show');
    Route::get('/penghapusan-aset', [AssetDisposalController::class, 'index'])->name('asset-disposals.index');
    Route::get('/penghapusan-aset/{disposal}', [AssetDisposalController::class, 'show'])->name('asset-disposals.show');
    Route::get('/penghapusan-aset/{disposal}/dokumen', [AssetDisposalController::class, 'document'])->name('asset-disposals.document');
    Route::get('/laporan-aset', [AssetReportController::class, 'index'])->name('asset-reports.index');
    Route::get('/laporan-aset/print', [AssetReportController::class, 'print'])->name('asset-reports.print');
    Route::get('/laporan-aset/pdf', [AssetReportController::class, 'pdf'])->name('asset-reports.pdf');
    Route::get('/laporan-aset/excel', [AssetReportController::class, 'excel'])->name('asset-reports.excel');
    Route::middleware('role:admin')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/pengguna/{user}/password', [UserController::class, 'password'])->name('users.password');
        Route::patch('/pengguna/{user}/status', [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('/kategori-aset', [AssetCategoryController::class, 'store'])->name('asset-categories.store');
        Route::put('/kategori-aset/{asset_category}', [AssetCategoryController::class, 'update'])->name('asset-categories.update');
        Route::patch('/kategori-aset/{asset_category}/status', [AssetCategoryController::class, 'toggle'])->name('asset-categories.toggle');
        Route::delete('/kategori-aset/{asset_category}', [AssetCategoryController::class, 'destroy'])->name('asset-categories.destroy');
        Route::post('/lokasi-ruangan', [AssetLocationController::class, 'store'])->name('asset-locations.store');
        Route::put('/lokasi-ruangan/{asset_location}', [AssetLocationController::class, 'update'])->name('asset-locations.update');
        Route::patch('/lokasi-ruangan/{asset_location}/status', [AssetLocationController::class, 'toggle'])->name('asset-locations.toggle');
        Route::delete('/lokasi-ruangan/{asset_location}', [AssetLocationController::class, 'destroy'])->name('asset-locations.destroy');
        Route::post('/sumber-dana', [FundingSourceController::class, 'store'])->name('funding-sources.store');
        Route::put('/sumber-dana/{funding_source}', [FundingSourceController::class, 'update'])->name('funding-sources.update');
        Route::patch('/sumber-dana/{funding_source}/status', [FundingSourceController::class, 'toggle'])->name('funding-sources.toggle');
        Route::delete('/sumber-dana/{funding_source}', [FundingSourceController::class, 'destroy'])->name('funding-sources.destroy');
        Route::get('/data-aset/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/data-aset', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/data-aset/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/data-aset/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::get('/cetak-label', [AssetLabelController::class, 'index'])->name('asset-labels.index');
        Route::post('/cetak-label/preview', [AssetLabelController::class, 'preview'])->name('asset-labels.preview');
        Route::get('/cetak-label/{asset}', [AssetLabelController::class, 'single'])->name('asset-labels.single');
        Route::get('/qr-code-aset', [AssetQrCodeController::class, 'index'])->name('asset-qr-codes.index');
        Route::get('/qr-code-aset/{asset}/download', [AssetQrCodeController::class, 'download'])->name('asset-qr-codes.download');
        Route::get('/qr-code-aset/{asset}/print', [AssetQrCodeController::class, 'print'])->name('asset-qr-codes.print');
        Route::post('/qr-code-aset/print-terpilih', [AssetQrCodeController::class, 'printSelected'])->name('asset-qr-codes.print-selected');
        Route::get('/pengaturan/identitas-sekolah', [SchoolSettingController::class, 'edit'])->name('school-settings.edit');
        Route::put('/pengaturan/identitas-sekolah', [SchoolSettingController::class, 'update'])->name('school-settings.update');
        Route::post('/mutasi-aset', [AssetMutationController::class, 'store'])->name('asset-mutations.store');
        Route::post('/peminjaman', [AssetLoanController::class, 'store'])->name('asset-loans.store');
        Route::patch('/peminjaman/{loan}/kembalikan', [AssetLoanController::class, 'returnAsset'])->name('asset-loans.return');
        Route::post('/perawatan', [AssetMaintenanceController::class, 'store'])->name('asset-maintenances.store');
        Route::patch('/perawatan/{maintenance}/mulai', [AssetMaintenanceController::class, 'start'])->name('asset-maintenances.start');
        Route::patch('/perawatan/{maintenance}/selesai', [AssetMaintenanceController::class, 'complete'])->name('asset-maintenances.complete');
        Route::patch('/perawatan/{maintenance}/batal', [AssetMaintenanceController::class, 'cancel'])->name('asset-maintenances.cancel');
        Route::post('/penghapusan-aset', [AssetDisposalController::class, 'store'])->name('asset-disposals.store');
    });
    Route::middleware('role:admin,kepala_sekolah')->group(function () {
        Route::patch('/penghapusan-aset/{disposal}/setujui', [AssetDisposalController::class, 'approve'])->name('asset-disposals.approve');
        Route::patch('/penghapusan-aset/{disposal}/tolak', [AssetDisposalController::class, 'reject'])->name('asset-disposals.reject');
    });
    Route::get('/data-aset/{asset}/foto', [AssetController::class, 'photo'])->name('assets.photo');
    Route::get('/data-aset/{asset}', [AssetController::class, 'show'])->name('assets.show');

    Route::get('/halaman/{page}', PlaceholderController::class)
        ->middleware('role:admin,kepala_sekolah')
        ->where('page', '[a-z-]+')->name('placeholder');
});
