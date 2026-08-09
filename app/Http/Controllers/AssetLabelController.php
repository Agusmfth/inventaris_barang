<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Services\AssetQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetLabelController extends Controller
{
    public function __construct(private readonly AssetQrCodeService $qr) {}
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'=>['nullable','string','max:100'], 'category'=>['nullable','integer','exists:asset_categories,id'],
            'location'=>['nullable','integer','exists:asset_locations,id'], 'condition'=>['nullable',Rule::in(Asset::CONDITIONS)],
            'year'=>['nullable','integer','min:1900','max:'.(now()->year + 1)],
        ]);
        $assets = Asset::with(['category:id,name','location:id,name'])
            ->when($filters['search'] ?? null, fn ($query,$search) => $query->where(fn ($q) => $q->where('asset_code','like',"%{$search}%")->orWhere('name','like',"%{$search}%")))
            ->when($filters['category'] ?? null, fn ($query,$id) => $query->where('category_id',$id))
            ->when($filters['location'] ?? null, fn ($query,$id) => $query->where('location_id',$id))
            ->when($filters['condition'] ?? null, fn ($query,$condition) => $query->where('condition',$condition))
            ->when($filters['year'] ?? null, fn ($query,$year) => $query->where('acquisition_year',$year))
            ->where('status','!=','dihapus')->latest()->paginate(10)->withQueryString();

        return view('asset-labels.index', [
            'assets'=>$assets, 'totalAssets'=>Asset::where('status','!=','dihapus')->count(),
            'categories'=>AssetCategory::orderBy('name')->get(['id','name']), 'locations'=>AssetLocation::orderBy('name')->get(['id','name']),
            'years'=>Asset::where('status','!=','dihapus')->select('acquisition_year')->distinct()->orderByDesc('acquisition_year')->pluck('acquisition_year'),
        ]);
    }

    public function single(Request $request, Asset $asset): View
    {
        $data = $request->validate(['size'=>['nullable',Rule::in(['small','medium','large'])], 'quantity'=>['nullable','integer','min:1','max:'.$asset->quantity]]);
        $asset->load(['category','location']);
        return $this->previewView(collect([$asset]), [$asset->id => $data['quantity'] ?? 1], $data['size'] ?? 'medium', $asset);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'asset_ids'=>['required','array','min:1'], 'asset_ids.*'=>['integer','distinct','exists:assets,id'],
            'quantities'=>['required','array'], 'quantities.*'=>['required','integer','min:1'], 'size'=>['required',Rule::in(['small','medium','large'])],
        ], [
            'asset_ids.required'=>'Pilih minimal satu aset untuk dicetak.','asset_ids.min'=>'Pilih minimal satu aset untuk dicetak.',
            'asset_ids.*.exists'=>'Aset yang dipilih tidak ditemukan.','quantities.*.required'=>'Jumlah label wajib diisi.',
            'quantities.*.integer'=>'Jumlah label harus berupa angka bulat.','quantities.*.min'=>'Jumlah label minimal 1.','size.in'=>'Ukuran label tidak valid.',
        ]);
        $assets = Asset::with(['category','location'])->whereIn('id',$data['asset_ids'])->get();
        foreach ($assets as $asset) {
            $amount = (int) ($data['quantities'][$asset->id] ?? 0);
            if ($amount < 1 || $amount > $asset->quantity) return back()->withInput()->withErrors(['quantities.'.$asset->id=>"Jumlah label {$asset->name} harus antara 1 dan {$asset->quantity}."]);
        }
        return $this->previewView($assets, $data['quantities'], $data['size']);
    }

    public function publicInfo(Asset $asset): View
    {
        return view('asset-labels.public-info', ['asset'=>$asset->load(['category','location'])]);
    }

    private function previewView(Collection $assets, array $quantities, string $size, ?Asset $singleAsset = null): View
    {
        $labels = collect();
        foreach ($assets as $asset) {
            $amount = (int) ($quantities[$asset->id] ?? 1);
            $qrDataUri = $this->qr->svgDataUri($asset,260);
            foreach (range(1,$amount) as $unit) $labels->push(compact('asset','unit','amount','qrDataUri'));
        }
        return view('asset-labels.preview', compact('labels','size','singleAsset'));
    }
}
