<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    public const CONDITIONS = ['baik', 'rusak_ringan', 'rusak_berat'];
    public const STATUSES = ['tersedia', 'dipinjam', 'perawatan', 'dihapus'];

    protected $fillable = ['asset_code', 'name', 'category_id', 'location_id', 'funding_source_id', 'brand', 'model', 'serial_number', 'acquisition_year', 'acquisition_date', 'acquisition_price', 'quantity', 'condition', 'status', 'photo', 'description', 'created_by'];
    protected $casts = ['acquisition_date' => 'date', 'acquisition_price' => 'decimal:2', 'acquisition_year' => 'integer', 'quantity' => 'integer'];

    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function location(): BelongsTo { return $this->belongsTo(AssetLocation::class, 'location_id'); }
    public function fundingSource(): BelongsTo { return $this->belongsTo(FundingSource::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function mutations(): HasMany { return $this->hasMany(AssetMutation::class); }
    public function loans(): HasMany { return $this->hasMany(AssetLoan::class); }
    public function maintenances(): HasMany { return $this->hasMany(AssetMaintenance::class); }
    public function disposals(): HasMany { return $this->hasMany(AssetDisposal::class); }

    public function getConditionLabelAttribute(): string { return match ($this->condition) { 'baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat' }; }
    public function getStatusLabelAttribute(): string { return match ($this->status) { 'tersedia' => 'Tersedia', 'dipinjam' => 'Dipinjam', 'perawatan' => 'Perawatan', 'dihapus' => 'Dihapus' }; }
}
