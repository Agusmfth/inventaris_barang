<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLoan;
use Illuminate\Database\Eloquent\Builder;

class AssetReportQuery
{
    public function build(array $filters): Builder
    {
        $query = Asset::query()->with(['category:id,name', 'location:id,name', 'fundingSource:id,name']);
        $status = $filters['status'] ?? 'active';
        if ($status === 'active') $query->where('status', '!=', 'dihapus');
        elseif ($status !== 'all') $query->where('status', $status);

        $query
            ->when($filters['year'] ?? null, fn (Builder $q, $v) => $q->where('acquisition_year', $v))
            ->when($filters['category'] ?? null, fn (Builder $q, $v) => $q->where('category_id', $v))
            ->when($filters['location'] ?? null, fn (Builder $q, $v) => $q->where('location_id', $v))
            ->when($filters['condition'] ?? null, fn (Builder $q, $v) => $q->where('condition', $v))
            ->when($filters['funding_source'] ?? null, fn (Builder $q, $v) => $q->where('funding_source_id', $v))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $v) => $q->whereDate('acquisition_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $v) => $q->whereDate('acquisition_date', '<=', $v))
            ->when($filters['search'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $n) => $n->where('asset_code', 'like', "%{$v}%")->orWhere('name', 'like', "%{$v}%")));

        return match ($filters['sort'] ?? 'code') {
            'name' => $query->orderBy('name')->orderBy('asset_code'),
            'year_desc' => $query->orderByDesc('acquisition_year')->orderBy('asset_code'),
            'year_asc' => $query->orderBy('acquisition_year')->orderBy('asset_code'),
            'value_desc' => $query->orderByRaw('(acquisition_price * quantity) DESC')->orderBy('asset_code'),
            'value_asc' => $query->orderByRaw('(acquisition_price * quantity) ASC')->orderBy('asset_code'),
            default => $query->orderBy('asset_code'),
        };
    }

    public function summary(array $filters): array
    {
        $filteredAssetIds = $this->build($filters)->reorder()->select('assets.id');
        $row = $this->build($filters)->reorder()->selectRaw(
            'COUNT(*) total_assets, COALESCE(SUM(quantity),0) total_units, COALESCE(SUM(acquisition_price * quantity),0) total_value, '
            ."COALESCE(SUM(CASE WHEN `condition`='baik' THEN quantity ELSE 0 END),0) good, "
            ."COALESCE(SUM(CASE WHEN `condition`='rusak_ringan' THEN quantity ELSE 0 END),0) light_damage, "
            ."COALESCE(SUM(CASE WHEN `condition`='rusak_berat' THEN quantity ELSE 0 END),0) heavy_damage, "
            ."COALESCE(SUM(CASE WHEN `status`='dihapus' THEN quantity ELSE 0 END),0) deleted"
        )->first();
        $summary = collect($row?->getAttributes() ?? [])->map(fn ($v) => (float) $v)->all();
        $summary['borrowed'] = (float) AssetLoan::whereNull('returned_at')->whereIn('asset_id', $filteredAssetIds)->sum('quantity');
        return $summary;
    }
}
