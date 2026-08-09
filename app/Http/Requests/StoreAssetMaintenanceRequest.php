<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array { return ['asset_id'=>['required','integer','exists:assets,id'], 'reported_date'=>['required','date'], 'issue'=>['required','string','max:3000'], 'initial_condition'=>['required','in:baik,rusak_ringan,rusak_berat'], 'maintenance_status'=>['nullable','in:menunggu'], 'service_location'=>['nullable','string','max:255'], 'technician'=>['nullable','string','max:255'], 'estimated_cost'=>['nullable','numeric','min:0','max:9999999999999.99'], 'start_date'=>['nullable','date','after_or_equal:reported_date'], 'notes'=>['nullable','string','max:3000']]; }
    public function messages(): array { return ['asset_id.required'=>'Aset wajib dipilih.', 'reported_date.required'=>'Tanggal laporan wajib diisi.', 'reported_date.date'=>'Tanggal laporan tidak valid.', 'issue.required'=>'Keluhan atau kerusakan wajib diisi.', 'initial_condition.required'=>'Kondisi awal wajib dipilih.', 'initial_condition.in'=>'Kondisi awal tidak valid.', 'estimated_cost.numeric'=>'Estimasi biaya harus berupa angka.', 'estimated_cost.min'=>'Estimasi biaya tidak boleh negatif.', 'start_date.after_or_equal'=>'Tanggal mulai tidak boleh sebelum tanggal laporan.']; }
}
