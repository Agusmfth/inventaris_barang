<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'code' => 'ELK', 'description' => 'Laptop, komputer, printer, proyektor dan perangkat elektronik lainnya.'],
            ['name' => 'Mebel', 'code' => 'MBL', 'description' => 'Meja, kursi, lemari dan perlengkapan furnitur sekolah.'],
            ['name' => 'Peralatan', 'code' => 'PRL', 'description' => 'Peralatan umum untuk mendukung kegiatan sekolah.'],
            ['name' => 'Alat Pendidikan', 'code' => 'PND', 'description' => 'Alat peraga dan perangkat pendukung kegiatan pembelajaran.'],
            ['name' => 'Perlengkapan Kantor', 'code' => 'KTR', 'description' => 'Perlengkapan administrasi dan operasional kantor sekolah.'],
            ['name' => 'Alat Olahraga', 'code' => 'OLR', 'description' => 'Peralatan untuk kegiatan olahraga dan pendidikan jasmani.'],
            ['name' => 'Lainnya', 'code' => 'LLN', 'description' => 'Aset yang tidak termasuk dalam kategori lainnya.'],
        ];

        foreach ($categories as $category) AssetCategory::updateOrCreate(['name' => $category['name']], $category + ['is_active' => true]);
    }
}
