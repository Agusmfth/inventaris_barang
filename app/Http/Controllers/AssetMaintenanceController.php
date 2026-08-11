<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteAssetMaintenanceRequest;
use App\Http\Requests\StoreAssetMaintenanceRequest;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Services\AssetMaintenanceService;
use App\Services\AssetNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'=>['nullable','string','max:100'],
            'status'=>['nullable','in:menunggu,diproses,selesai,dibatalkan'],
            'condition'=>['nullable','in:baik,rusak_ringan,rusak_berat'],
            'date'=>['nullable','date'],
            'location'=>['nullable','integer','exists:asset_locations,id'],
            'per_page'=>['nullable','integer','in:10,20,50,100']
        ]);
        $perPage = $request->integer('per_page', 10);
        $maintenances = AssetMaintenance::with(['asset.location:id,name'])
            ->when($filters['search'] ?? null, fn($q,$s)=>$q->whereHas('asset', fn($a)=>$a->where('asset_code','like',"%{$s}%")->orWhere('name','like',"%{$s}%")))
            ->when($filters['status'] ?? null, fn($q,$v)=>$q->where('maintenance_status',$v))
            ->when($filters['condition'] ?? null, fn($q,$v)=>$q->where('initial_condition',$v))
            ->when($filters['date'] ?? null, fn($q,$v)=>$q->whereDate('reported_date',$v))
            ->when($filters['location'] ?? null, fn($q,$v)=>$q->whereHas('asset',fn($a)=>$a->where('location_id',$v)))
            ->latest('reported_date')->latest('id')->paginate($perPage)->withQueryString();
        $assets = Asset::with('location:id,name')->whereIn('condition',['rusak_ringan','rusak_berat'])->whereNotIn('status',['dipinjam','dihapus'])->whereDoesntHave('loans',fn($q)=>$q->whereNull('returned_at'))->whereDoesntHave('maintenances',fn($q)=>$q->whereIn('maintenance_status',AssetMaintenance::ACTIVE_STATUSES))->orderBy('name')->get();
        return view('asset-maintenances.index', ['maintenances'=>$maintenances, 'assets'=>$assets, 'locations'=>AssetLocation::where('is_active',true)->orderBy('name')->get(['id','name']), 'waitingCount'=>AssetMaintenance::where('maintenance_status','menunggu')->count(), 'processingCount'=>AssetMaintenance::where('maintenance_status','diproses')->count(), 'completedThisMonth'=>AssetMaintenance::where('maintenance_status','selesai')->whereYear('completed_date',now()->year)->whereMonth('completed_date',now()->month)->count(), 'totalCost'=>(float)AssetMaintenance::where('maintenance_status','selesai')->sum('actual_cost')]);
    }

    public function store(StoreAssetMaintenanceRequest $request, AssetMaintenanceService $service, AssetNotificationService $notifications): RedirectResponse
    {
        $maintenance = $service->create($request->validated(), $request->user());
        $maintenance->load('asset');
        $notifications->heads('Aset Masuk Perawatan', $maintenance->asset->name.' dilaporkan mengalami kerusakan.', 'maintenance_created', route('asset-maintenances.show',$maintenance), 'maintenance', $maintenance->id, 'maintenance_created:'.$maintenance->id);
        return redirect()->route('asset-maintenances.show',$maintenance)->with('success','Perawatan aset berhasil ditambahkan.');
    }

    public function show(AssetMaintenance $maintenance): View
    {
        return view('asset-maintenances.show',['maintenance'=>$maintenance->load(['asset.location','creator','starter','completer','canceller'])]);
    }

    public function start(AssetMaintenance $maintenance, AssetMaintenanceService $service): RedirectResponse
    {
        $service->start($maintenance, request()->user());
        return redirect()->route('asset-maintenances.show',$maintenance)->with('success','Perawatan mulai diproses.');
    }

    public function complete(CompleteAssetMaintenanceRequest $request, AssetMaintenance $maintenance, AssetMaintenanceService $service, AssetNotificationService $notifications): RedirectResponse
    {
        $service->complete($maintenance,$request->validated(),$request->user());
        $maintenance->load('asset');
        $notifications->admins('Perawatan Selesai', $maintenance->asset->name.' telah selesai diperbaiki.', 'maintenance_completed', route('asset-maintenances.show',$maintenance), 'maintenance', $maintenance->id, 'maintenance_completed:'.$maintenance->id);
        return redirect()->route('asset-maintenances.show',$maintenance)->with('success','Perawatan berhasil diselesaikan.');
    }

    public function cancel(AssetMaintenance $maintenance, AssetMaintenanceService $service): RedirectResponse
    {
        $service->cancel($maintenance, request()->user());
        return redirect()->route('asset-maintenances.show',$maintenance)->with('success','Perawatan dibatalkan.');
    }
}
