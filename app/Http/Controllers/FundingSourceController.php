<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundingSourceRequest;
use App\Http\Requests\UpdateFundingSourceRequest;
use App\Models\FundingSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundingSourceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:active,inactive']]);
        $sources = FundingSource::query()->withSum(['assets as assets_count'=>fn($q)=>$q->where('status','!=','dihapus')], 'quantity')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('funding-sources.index', ['sources' => $sources, 'totalSources' => FundingSource::count(), 'activeSources' => FundingSource::where('is_active', true)->count(), 'totalAssets' => \App\Models\Asset::where('status','!=','dihapus')->sum('quantity')]);
    }
    public function store(StoreFundingSourceRequest $request): RedirectResponse
    {
        FundingSource::create($request->validated());
        return redirect()->route('funding-sources.index')->with('success', 'Sumber dana berhasil ditambahkan.');
    }
    public function update(UpdateFundingSourceRequest $request, FundingSource $fundingSource): RedirectResponse
    {
        $fundingSource->update($request->validated());
        return redirect()->route('funding-sources.index')->with('success', 'Sumber dana berhasil diperbarui.');
    }
    public function toggle(FundingSource $fundingSource): RedirectResponse
    {
        $fundingSource->update(['is_active' => ! $fundingSource->is_active]);
        return back()->with('success', $fundingSource->is_active ? 'Sumber dana berhasil diaktifkan.' : 'Sumber dana berhasil dinonaktifkan.');
    }
    public function destroy(FundingSource $fundingSource): RedirectResponse
    {
        if ($fundingSource->assets()->exists()) return back()->with('error', 'Sumber dana tidak dapat dihapus karena masih digunakan oleh aset.');
        $fundingSource->delete();
        return redirect()->route('funding-sources.index')->with('success', 'Sumber dana berhasil dihapus.');
    }
}
