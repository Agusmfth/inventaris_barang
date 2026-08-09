<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMutation;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetDisposal;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $inventory = Asset::query()->where('status', '!=', 'dihapus');
        $total = (clone $inventory)->sum('quantity');
        $good = (clone $inventory)->where('condition','baik')->sum('quantity');
        $light = (clone $inventory)->where('condition','rusak_ringan')->sum('quantity');
        $heavy = (clone $inventory)->where('condition','rusak_berat')->sum('quantity');
        $loaned = (int) AssetLoan::whereNull('returned_at')->sum('quantity');
        $totalValue = (float) (clone $inventory)->selectRaw('COALESCE(SUM(acquisition_price * quantity),0) AS total')->value('total');
        $percent = fn (int $value): string => $total > 0 ? number_format(($value / $total) * 100, 1, ',', '.').'%' : '0%';

        $summary = [
            ['label'=>'TOTAL ASET','value'=>number_format($total,0,',','.'),'unit'=>'Barang','description'=>'Total nilai aset','detail'=>'Rp '.number_format($totalValue,0,',','.'),'tone'=>'emerald','icon'=>'package'],
            ['label'=>'KONDISI BAIK','value'=>number_format($good,0,',','.'),'unit'=>'Barang','description'=>$percent($good).' dari total aset','detail'=>null,'tone'=>'green','icon'=>'circle-check'],
            ['label'=>'RUSAK RINGAN','value'=>number_format($light,0,',','.'),'unit'=>'Barang','description'=>$percent($light).' dari total aset','detail'=>null,'tone'=>'amber','icon'=>'triangle-alert'],
            ['label'=>'RUSAK BERAT','value'=>number_format($heavy,0,',','.'),'unit'=>'Barang','description'=>$percent($heavy).' dari total aset','detail'=>null,'tone'=>'red','icon'=>'circle-x'],
            ['label'=>'DIPINJAM','value'=>number_format($loaned,0,',','.'),'unit'=>'Barang','description'=>$percent($loaned).' dari total aset','detail'=>null,'tone'=>'indigo','icon'=>'repeat-2'],
        ];

        $categoryData = AssetCategory::withSum(['assets as asset_quantity' => fn ($q) => $q->where('status','!=','dihapus')], 'quantity')->orderByDesc('asset_quantity')->get()->filter(fn ($category) => $category->asset_quantity > 0)->values();
        $palette = ['#2563EB','#059669','#F59E0B','#7C3AED','#94A3B8','#0891B2','#DB2777'];
        $charts = [
            'total'=>$total,
            'categories'=>['labels'=>$categoryData->pluck('name')->all(),'values'=>$categoryData->pluck('asset_quantity')->map(fn($v)=>(int)$v)->all(),'colors'=>$categoryData->keys()->map(fn($i)=>$palette[$i % count($palette)])->all()],
            'conditions'=>['labels'=>['Baik','Rusak Ringan','Rusak Berat','Dipinjam'],'values'=>[$good,$light,$heavy,$loaned],'colors'=>['#059669','#F59E0B','#EF4444','#6366F1']],
        ];

        $latestAssets = Asset::with(['category:id,name','location:id,name'])->where('status','!=','dihapus')->latest()->limit(5)->get()->map(fn($asset) => [
            'code'=>$asset->asset_code,'name'=>$asset->name,'category'=>$asset->category->name,'location'=>$asset->location->name,'condition'=>$asset->condition_label,'condition_key'=>$asset->condition,'date'=>$asset->created_at->locale('id')->translatedFormat('d F Y'),
        ]);
        $activities = $this->activities();
        return view('dashboard', compact('summary','charts','latestAssets','activities'));
    }

    private function activities(): Collection
    {
        $assets = Asset::latest()->limit(5)->get()->map(fn($asset) => ['type'=>'Aset baru ditambahkan','description'=>$asset->name,'time'=>$asset->created_at->diffForHumans(),'at'=>$asset->created_at,'icon'=>'plus','tone'=>'green']);
        $mutations = AssetMutation::with(['asset:id,name','fromLocation:id,name','toLocation:id,name'])->latest()->limit(5)->get()->map(fn($mutation) => ['type'=>'Mutasi aset','description'=>$mutation->asset->name.' dipindahkan dari '.$mutation->fromLocation->name.' ke '.$mutation->toLocation->name,'time'=>$mutation->created_at->diffForHumans(),'at'=>$mutation->created_at,'icon'=>'arrow-left-right','tone'=>'green']);
        $loans = AssetLoan::with('asset:id,name')->latest()->limit(5)->get()->flatMap(function($loan){$items=collect([['type'=>'Peminjaman aset','description'=>$loan->asset->name.' dipinjam oleh '.$loan->borrower_name.'.','time'=>$loan->created_at->diffForHumans(),'at'=>$loan->created_at,'icon'=>'clipboard-list','tone'=>'green']]);if($loan->returned_at)$items->push(['type'=>'Pengembalian aset','description'=>$loan->asset->name.' telah dikembalikan.','time'=>$loan->returned_at->diffForHumans(),'at'=>$loan->returned_at,'icon'=>'undo-2','tone'=>'green']);return $items;});
        $maintenances = AssetMaintenance::with('asset:id,name')->latest()->limit(5)->get()->flatMap(function($maintenance){$items=collect();if($maintenance->start_date)$items->push(['type'=>'Perawatan aset','description'=>'Perawatan '.$maintenance->asset->name.' dimulai.','time'=>$maintenance->start_date->diffForHumans(),'at'=>$maintenance->start_date,'icon'=>'wrench','tone'=>'amber']);if($maintenance->completed_date)$items->push(['type'=>'Perawatan selesai','description'=>'Perawatan '.$maintenance->asset->name.' selesai.','time'=>$maintenance->completed_date->diffForHumans(),'at'=>$maintenance->completed_date,'icon'=>'circle-check','tone'=>'green']);return $items;});
        $disposals = AssetDisposal::with('asset:id,name')->latest()->limit(5)->get()->map(fn($disposal)=>['type'=>'Penghapusan aset','description'=>$disposal->asset->name.' '.$disposal->status_label.'.','time'=>($disposal->approved_at ?: $disposal->created_at)->diffForHumans(),'at'=>$disposal->approved_at ?: $disposal->created_at,'icon'=>'trash-2','tone'=>$disposal->status==='disetujui'?'red':'amber']);
        return $assets->concat($mutations)->concat($loans)->concat($maintenances)->concat($disposals)->sortByDesc('at')->take(5)->values();
    }
}
