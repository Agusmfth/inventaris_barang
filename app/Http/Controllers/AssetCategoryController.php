<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100']
        ]);
        $perPage = $request->integer('per_page', 10);
        $categories = AssetCategory::query()->withSum(['assets as assets_count'=>fn($q)=>$q->where('status','!=','dihapus')], 'quantity')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')->paginate($perPage)->withQueryString();

        return view('asset-categories.index', [
            'categories' => $categories,
            'totalCategories' => AssetCategory::count(),
            'activeCategories' => AssetCategory::where('is_active', true)->count(),
        ]);
    }

    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        AssetCategory::create($request->validated());
        return redirect()->route('asset-categories.index')->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update($request->validated());
        return redirect()->route('asset-categories.index')->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function toggle(AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update(['is_active' => ! $assetCategory->is_active]);
        return back()->with('success', $assetCategory->is_active ? 'Kategori aset berhasil diaktifkan.' : 'Kategori aset berhasil dinonaktifkan.');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        if ($assetCategory->assets()->exists()) return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh aset.');
        $assetCategory->delete();
        return redirect()->route('asset-categories.index')->with('success', 'Kategori aset berhasil dihapus.');
    }
}
