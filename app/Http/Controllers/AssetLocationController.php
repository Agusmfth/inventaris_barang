<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetLocationRequest;
use App\Http\Requests\UpdateAssetLocationRequest;
use App\Models\AssetLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetLocationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100']
        ]);
        $perPage = $request->integer('per_page', 10);
        $locations = AssetLocation::query()->withSum(['assets as assets_count'=>fn($q)=>$q->where('status','!=','dihapus')], 'quantity')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('person_in_charge', 'like', "%{$search}%")))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('code')->paginate($perPage)->withQueryString();

        return view('asset-locations.index', [
            'locations' => $locations,
            'totalLocations' => AssetLocation::count(),
            'activeLocations' => AssetLocation::where('is_active', true)->count(),
            'totalAssets' => \App\Models\Asset::where('status','!=','dihapus')->sum('quantity'),
        ]);
    }

    public function store(StoreAssetLocationRequest $request): RedirectResponse
    {
        AssetLocation::create($request->validated());
        return redirect()->route('asset-locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function update(UpdateAssetLocationRequest $request, AssetLocation $assetLocation): RedirectResponse
    {
        $assetLocation->update($request->validated());
        return redirect()->route('asset-locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function toggle(AssetLocation $assetLocation): RedirectResponse
    {
        $assetLocation->update(['is_active' => ! $assetLocation->is_active]);
        return back()->with('success', $assetLocation->is_active ? 'Lokasi berhasil diaktifkan.' : 'Lokasi berhasil dinonaktifkan.');
    }

    public function destroy(AssetLocation $assetLocation): RedirectResponse
    {
        if ($assetLocation->assets()->exists()) return back()->with('error', 'Lokasi tidak dapat dihapus karena masih digunakan oleh aset.');
        $assetLocation->delete();
        return redirect()->route('asset-locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
