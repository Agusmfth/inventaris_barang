<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class AssetMaintenanceService
{
    public function create(array $data, User $user): AssetMaintenance
    {
        return DB::transaction(function () use ($data, $user) {
            $asset = Asset::lockForUpdate()->findOrFail($data['asset_id']);
            $hasActiveMaintenance = AssetMaintenance::where('asset_id', $asset->id)->whereIn('maintenance_status', AssetMaintenance::ACTIVE_STATUSES)->exists();
            if ($asset->loans()->whereNull('returned_at')->exists() || $asset->status === 'dipinjam') throw ValidationException::withMessages(['asset_id'=>'Aset yang sedang dipinjam harus dikembalikan sebelum masuk perawatan.']);
            if ($asset->status === 'dihapus') throw ValidationException::withMessages(['asset_id'=>'Aset yang sudah dihapus tidak dapat dirawat.']);
            if (!in_array($asset->condition, ['rusak_ringan', 'rusak_berat'], true)) throw ValidationException::withMessages(['asset_id'=>'Hanya aset berkondisi Rusak Ringan atau Rusak Berat yang dapat masuk perawatan.']);
            if ($hasActiveMaintenance) throw ValidationException::withMessages(['asset_id'=>'Aset sudah memiliki perawatan aktif.']);
            $maintenance = AssetMaintenance::create([...$data, 'initial_condition'=>$asset->condition, 'maintenance_status'=>'menunggu', 'estimated_cost'=>$data['estimated_cost'] ?? 0, 'created_by'=>$user->id]);
            $asset->update(['status'=>'perawatan']);
            return $maintenance;
        });
    }

    public function start(AssetMaintenance $maintenance, User $user): void
    {
        DB::transaction(function () use ($maintenance, $user) {
            $locked = AssetMaintenance::lockForUpdate()->findOrFail($maintenance->id);
            if ($locked->maintenance_status !== 'menunggu') throw ValidationException::withMessages(['maintenance'=>'Hanya perawatan berstatus menunggu yang dapat mulai diproses.']);
            $locked->update(['maintenance_status'=>'diproses', 'start_date'=>$locked->start_date ?: today(), 'started_at'=>now(), 'started_by'=>$user->id]);
        });
    }

    public function complete(AssetMaintenance $maintenance, array $data, User $user): void
    {
        DB::transaction(function () use ($maintenance, $data, $user) {
            $locked = AssetMaintenance::lockForUpdate()->findOrFail($maintenance->id);
            if ($locked->maintenance_status !== 'diproses') throw ValidationException::withMessages(['maintenance'=>'Hanya perawatan yang sedang diproses yang dapat diselesaikan.']);
            $minimumDate = $locked->start_date ?: $locked->reported_date;
            if (Carbon::parse($data['completed_date'])->startOfDay()->lt($minimumDate->copy()->startOfDay())) throw ValidationException::withMessages(['completed_date'=>'Tanggal selesai tidak boleh sebelum tanggal mulai perawatan.']);
            $asset = Asset::lockForUpdate()->findOrFail($locked->asset_id);
            $locked->update([...$data, 'actual_cost'=>$data['actual_cost'] ?? 0, 'maintenance_status'=>'selesai', 'completed_by'=>$user->id]);
            $asset->update(['condition'=>$data['final_condition'], 'status'=>'tersedia']);
        });
    }

    public function cancel(AssetMaintenance $maintenance, User $user): void
    {
        DB::transaction(function () use ($maintenance, $user) {
            $locked = AssetMaintenance::lockForUpdate()->findOrFail($maintenance->id);
            if ($locked->maintenance_status !== 'menunggu') throw ValidationException::withMessages(['maintenance'=>'Hanya perawatan berstatus menunggu yang dapat dibatalkan.']);
            $asset = Asset::lockForUpdate()->findOrFail($locked->asset_id);
            $locked->update(['maintenance_status'=>'dibatalkan', 'cancelled_at'=>now(), 'cancelled_by'=>$user->id]);
            $asset->update(['status'=>'tersedia']);
        });
    }
}
