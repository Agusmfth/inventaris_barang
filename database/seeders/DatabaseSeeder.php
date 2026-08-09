<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::firstOrCreate([], ['school_name'=>'SD Negeri Sayar','inventory_label_title'=>'BARANG INVENTARIS','inventory_label_footer'=>'JAGA & GUNAKAN DENGAN BAIK']);
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator', 'email' => 'admin@sdnegerisayar.sch.id',
            'password' => Hash::make('admin123'), 'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'kepsek'], [
            'name' => 'Kepala Sekolah', 'email' => 'kepsek@sdnegerisayar.sch.id',
            'password' => Hash::make('kepsek123'), 'role' => User::ROLE_KEPALA_SEKOLAH, 'is_active' => true,
        ]);

        $this->call(AssetCategorySeeder::class);
        $this->call(AssetLocationSeeder::class);
        $this->call(FundingSourceSeeder::class);
        $this->call(AssetSeeder::class);
    }
}
