<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\FundingSource;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('username','admin')->value('id');
        $category = fn(string $name) => AssetCategory::where('name',$name)->value('id');
        $location = fn(string $name) => AssetLocation::where('name',$name)->value('id');
        $fund = fn(string $code) => FundingSource::where('code',$code)->value('id');
        $assets = [
            ['AST-2026-0001','Laptop ASUS X415','Elektronik','Ruang Administrasi','BOS','ASUS','X415','SN-ASUS-001',2026,'2026-05-20',7500000,1,'baik','tersedia'],
            ['AST-2026-0002','Proyektor Epson EB-X06','Elektronik','Ruang Rapat','BOS','Epson','EB-X06','SN-EPS-002',2026,'2026-05-19',6800000,1,'baik','dipinjam'],
            ['AST-2026-0003','Printer Epson L3210','Elektronik','Ruang Administrasi','BOS-K','Epson','L3210','SN-EPS-003',2026,'2026-05-18',3200000,1,'rusak_ringan','perawatan'],
            ['AST-2026-0004','Meja Pelayanan','Mebel','Ruang Pelayanan Anggota','DAK',null,null,null,2026,'2026-05-18',1250000,6,'baik','tersedia'],
            ['AST-2026-0005','Kursi Pelayanan','Mebel','Ruang Pelayanan Anggota','DAK',null,null,null,2026,'2026-05-17',450000,30,'baik','tersedia'],
            ['AST-2026-0006','PC Kasir','Elektronik','Ruang Kasir','APBD','Lenovo','ThinkCentre',null,2026,'2026-04-12',8500000,12,'baik','tersedia'],
            ['AST-2026-0007','Lemari Arsip','Mebel','Ruang Administrasi','BOS',null,null,null,2026,'2026-03-10',2100000,2,'rusak_berat','tersedia'],
            ['AST-2026-0008','Televisi LED','Elektronik','Ruang Pengurus','HIB','Samsung','43 inch',null,2026,'2026-02-05',5200000,1,'baik','tersedia'],
        ];
        foreach ($assets as [$code,$name,$cat,$loc,$fundCode,$brand,$model,$serial,$year,$date,$price,$qty,$condition,$status]) {
            Asset::updateOrCreate(['asset_code'=>$code], ['name'=>$name,'category_id'=>$category($cat),'location_id'=>$location($loc),'funding_source_id'=>$fund($fundCode),'brand'=>$brand,'model'=>$model,'serial_number'=>$serial,'acquisition_year'=>$year,'acquisition_date'=>$date,'acquisition_price'=>$price,'quantity'=>$qty,'condition'=>$condition,'status'=>$status,'created_by'=>$creator]);
        }
    }
}
