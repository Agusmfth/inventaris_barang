<?php

namespace Database\Seeders;

use App\Models\FundingSource;
use Illuminate\Database\Seeder;

class FundingSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['BOS', 'BOS Reguler', 'Dana Bantuan Operasional Satuan Pendidikan.'],
            ['BOS-K', 'BOS Kinerja', 'Dana BOS berdasarkan pencapaian dan kinerja sekolah.'],
            ['APBD', 'APBD', 'Pengadaan yang bersumber dari anggaran pemerintah daerah.'],
            ['APBN', 'APBN', 'Pengadaan yang bersumber dari anggaran pemerintah pusat.'],
            ['DAK', 'DAK', 'Dana Alokasi Khusus untuk pengadaan sarana dan prasarana.'],
            ['HIB', 'Hibah', 'Barang yang diperoleh melalui hibah dari pihak lain.'],
            ['KOM', 'Komite Sekolah', 'Pembiayaan yang bersumber dari komite sekolah.'],
            ['SWD', 'Swadaya', 'Pembiayaan swadaya warga dan lingkungan sekolah.'],
            ['LLN', 'Lainnya', 'Sumber pembiayaan lain yang belum terklasifikasi.'],
        ];
        foreach ($sources as [$code, $name, $description]) FundingSource::updateOrCreate(['code' => $code], ['name' => $name, 'description' => $description, 'is_active' => true]);
    }
}
