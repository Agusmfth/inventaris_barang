<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    public const ACTIVE_STATUSES = ['menunggu', 'diproses'];

    protected $fillable = ['asset_id', 'reported_date', 'issue', 'initial_condition', 'maintenance_status', 'service_location', 'technician', 'estimated_cost', 'actual_cost', 'start_date', 'started_at', 'started_by', 'completed_date', 'action_taken', 'final_condition', 'notes', 'created_by', 'completed_by', 'cancelled_at', 'cancelled_by'];
    protected $casts = ['reported_date'=>'date', 'start_date'=>'date', 'started_at'=>'datetime', 'completed_date'=>'date', 'cancelled_at'=>'datetime', 'estimated_cost'=>'decimal:2', 'actual_cost'=>'decimal:2'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function completer(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
    public function starter(): BelongsTo { return $this->belongsTo(User::class, 'started_by'); }
    public function canceller(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }
    public function getStatusLabelAttribute(): string { return match ($this->maintenance_status) { 'menunggu'=>'Menunggu', 'diproses'=>'Sedang Diproses', 'selesai'=>'Selesai', 'dibatalkan'=>'Dibatalkan' }; }
    public function getInitialConditionLabelAttribute(): string { return $this->conditionLabel($this->initial_condition); }
    public function getFinalConditionLabelAttribute(): string { return $this->final_condition ? $this->conditionLabel($this->final_condition) : '—'; }
    private function conditionLabel(string $condition): string { return match ($condition) { 'baik'=>'Baik', 'rusak_ringan'=>'Rusak Ringan', 'rusak_berat'=>'Rusak Berat' }; }
}
