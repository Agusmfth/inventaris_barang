<?php

namespace Database\Seeders;

use App\Models\AssetLocation;
use Illuminate\Database\Seeder;

class AssetLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['R001', 'Ruang Pengurus', 'Ketua Koperasi', 'Ruang kerja pengurus koperasi desa.'],
            ['R002', 'Ruang Pelayanan Anggota', 'Petugas Pelayanan', 'Area pelayanan administrasi dan kebutuhan anggota koperasi.'],
            ['R003', 'Ruang Administrasi', 'Kepala Administrasi', 'Ruang pengelolaan dokumen dan administrasi koperasi.'],
            ['R004', 'Toko Koperasi', 'Kepala Toko', 'Area penjualan barang kebutuhan anggota dan masyarakat.'],
            ['R005', 'Ruang Kasir', 'Kasir', 'Area transaksi dan pencatatan penjualan koperasi.'],
            ['R006', 'Gudang Sembako', 'Petugas Gudang', 'Ruang penyimpanan persediaan bahan pokok.'],
            ['R007', 'Gudang Pupuk', 'Petugas Gudang', 'Ruang penyimpanan pupuk dan sarana pertanian.'],
            ['R008', 'Gudang Peralatan', 'Petugas Gudang', 'Ruang penyimpanan peralatan operasional koperasi.'],
            ['R009', 'Area Display Produk', 'Pramuniaga', 'Area pajang produk unggulan desa dan barang dagangan.'],
            ['R010', 'Ruang Rapat', 'Sekretaris Koperasi', 'Ruang rapat pengurus dan anggota koperasi.'],
            ['R011', 'Ruang Produksi', 'Koordinator Produksi', 'Area pengolahan atau pengemasan produk koperasi.'],
            ['R012', 'Ruang Penyimpanan Dingin', 'Petugas Gudang', 'Area penyimpanan produk yang memerlukan suhu terkontrol.'],
            ['R013', 'Gudang Utama', 'Kepala Gudang', 'Ruang penyimpanan utama barang dan inventaris koperasi desa.'],
        ];
        foreach ($locations as [$code, $name, $person, $description]) {
            AssetLocation::updateOrCreate(['code' => $code], ['name' => $name, 'person_in_charge' => $person, 'description' => $description, 'is_active' => true]);
        }
    }
}
