<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\FundingSource;
use App\Services\AssetCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable','string','max:100'], 'category' => ['nullable','integer','exists:asset_categories,id'],
            'location' => ['nullable','integer','exists:asset_locations,id'], 'condition' => ['nullable','in:baik,rusak_ringan,rusak_berat'],
            'status' => ['nullable','in:tersedia,dipinjam,perawatan,dihapus'], 'year' => ['nullable','integer','min:1900','max:'.(now()->year + 1)],
        ]);
        $assets = Asset::with(['category:id,name', 'location:id,name'])
            ->when($filters['search'] ?? null, fn ($q,$search) => $q->where(fn ($n) => $n->where('asset_code','like',"%{$search}%")->orWhere('name','like',"%{$search}%")->orWhere('brand','like',"%{$search}%")->orWhere('model','like',"%{$search}%")->orWhere('serial_number','like',"%{$search}%")))
            ->when($filters['category'] ?? null, fn ($q,$id) => $q->where('category_id',$id))
            ->when($filters['location'] ?? null, fn ($q,$id) => $q->where('location_id',$id))
            ->when($filters['condition'] ?? null, fn ($q,$value) => $q->where('condition',$value))
            ->when($filters['status'] ?? null, fn ($q,$value) => $q->where('status',$value))
            ->when($filters['year'] ?? null, fn ($q,$value) => $q->where('acquisition_year',$value))
            ->latest()->paginate(10)->withQueryString();

        return view('assets.index', [
            'assets'=>$assets, 'categories'=>AssetCategory::orderBy('name')->get(['id','name']), 'locations'=>AssetLocation::orderBy('name')->get(['id','name']),
            'years'=>Asset::select('acquisition_year')->distinct()->orderByDesc('acquisition_year')->pluck('acquisition_year'),
            'summary'=>['total'=>Asset::where('status','!=','dihapus')->sum('quantity'),'good'=>Asset::where('status','!=','dihapus')->where('condition','baik')->sum('quantity'),'light'=>Asset::where('status','!=','dihapus')->where('condition','rusak_ringan')->sum('quantity'),'heavy'=>Asset::where('status','!=','dihapus')->where('condition','rusak_berat')->sum('quantity')],
        ]);
    }

    public function create(): View
    {
        return view('assets.create', [
            'categories'=>AssetCategory::where('is_active',true)->orderBy('name')->get(),
            'locations'=>AssetLocation::where('is_active',true)->orderBy('name')->get(),
            'fundingSources'=>FundingSource::where('is_active',true)->orderBy('name')->get(),
        ]);
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location', 'fundingSource', 'creator', 'mutations.fromLocation:id,name', 'mutations.toLocation:id,name', 'mutations.creator:id,name', 'loans', 'maintenances', 'disposals']);
        $assetActivities = $asset->mutations->map(fn($m)=>['date'=>$m->mutation_date,'title'=>'Mutasi Aset','description'=>$m->fromLocation->name.' → '.$m->toLocation->name,'meta'=>'Oleh '.($m->creator?->name ?: 'Pengguna tidak tersedia'),'icon'=>'arrow-left-right','url'=>route('asset-mutations.show',$m)])
            ->concat($asset->loans->flatMap(function($loan){$items=collect([['date'=>$loan->loan_date,'title'=>'Peminjaman','description'=>'Dipinjam oleh '.$loan->borrower_name,'meta'=>$loan->expected_return_date?'Rencana kembali '.$loan->expected_return_date->locale('id')->translatedFormat('d M Y'):'Tanpa rencana kembali','icon'=>'clipboard-list','url'=>route('asset-loans.show',$loan)]]);if($loan->returned_at)$items->push(['date'=>$loan->returned_at,'title'=>'Pengembalian','description'=>'Dikembalikan oleh '.$loan->borrower_name,'meta'=>'Kondisi: '.match($loan->return_condition){'baik'=>'Baik','rusak_ringan'=>'Rusak Ringan','rusak_berat'=>'Rusak Berat',default=>'—'},'icon'=>'undo-2','url'=>route('asset-loans.show',$loan)]);return $items;}))
            ->concat($asset->maintenances->flatMap(function($maintenance){$items=collect([['date'=>$maintenance->reported_date,'title'=>'Perawatan','description'=>'Aset dilaporkan rusak','meta'=>'Keluhan: '.$maintenance->issue.' · Status: '.$maintenance->status_label,'icon'=>'wrench','url'=>route('asset-maintenances.show',$maintenance)]]);if($maintenance->completed_date)$items->push(['date'=>$maintenance->completed_date,'title'=>'Perawatan Selesai','description'=>'Tindakan: '.($maintenance->action_taken ?: '—'),'meta'=>'Biaya: Rp '.number_format((float)$maintenance->actual_cost,0,',','.').' · Kondisi akhir: '.$maintenance->final_condition_label,'icon'=>'circle-check','url'=>route('asset-maintenances.show',$maintenance)]);return $items;}))
            ->concat($asset->disposals->map(fn($disposal)=>['date'=>$disposal->approved_at ?: $disposal->disposal_date,'title'=>'Penghapusan Aset','description'=>$disposal->method_label,'meta'=>'Alasan: '.$disposal->reason.' · Status: '.$disposal->status_label,'icon'=>'trash-2','url'=>route('asset-disposals.show',$disposal)]))->sortByDesc('date')->values();
        return view('assets.show', compact('asset','assetActivities'));
    }

    public function photo(Asset $asset)
    {
        abort_unless($asset->photo && Storage::disk('public')->exists($asset->photo), 404);

        return Storage::disk('public')->response($asset->photo, null, [
            'Cache-Control' => 'private, max-age=86400',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function edit(Asset $asset): View
    {
        abort_if($asset->status === 'dihapus', 403, 'Aset yang sudah dihapus tidak dapat diedit.');
        return view('assets.edit', [
            'asset' => $asset,
            'categories' => AssetCategory::where('is_active', true)->orWhere('id', $asset->category_id)->orderBy('name')->get(),
            'locations' => AssetLocation::where('is_active', true)->orWhere('id', $asset->location_id)->orderBy('name')->get(),
            'fundingSources' => FundingSource::where('is_active', true)->when($asset->funding_source_id, fn ($query) => $query->orWhere('id', $asset->funding_source_id))->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAssetRequest $request, AssetCodeGenerator $generator): RedirectResponse
    {
        $data = $request->validated();
        $photoPath = $request->file('photo')?->store('assets', 'public');
        unset($data['photo']);
        if ($photoPath) $data['photo'] = $photoPath;
        $data['created_by'] = $request->user()->id;

        try {
            retry(3, function () use ($data, $generator) {
                DB::transaction(function () use ($data, $generator) {
                    $data['asset_code'] = $generator->generate((int) $data['acquisition_year']);
                    Asset::create($data);
                });
            }, 50);
        } catch (Throwable $exception) {
            if ($photoPath) Storage::disk('public')->delete($photoPath);
            throw $exception;
        }
        return redirect()->route('assets.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        abort_if($asset->status === 'dihapus', 403, 'Aset yang sudah dihapus tidak dapat diedit.');
        $data = $request->validated();
        $newPhoto = $request->file('photo')?->store('assets', 'public');
        unset($data['photo']);
        if ($newPhoto) $data['photo'] = $newPhoto;
        $oldPhoto = $asset->photo;
        try {
            DB::transaction(fn () => $asset->update($data));
        } catch (Throwable $exception) {
            if ($newPhoto) Storage::disk('public')->delete($newPhoto);
            throw $exception;
        }
        if ($newPhoto && $oldPhoto) Storage::disk('public')->delete($oldPhoto);
        return redirect()->route('assets.show', $asset)->with('success', 'Aset berhasil diperbarui.');
    }
}
