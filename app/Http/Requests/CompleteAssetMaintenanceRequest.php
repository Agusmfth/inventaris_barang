<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteAssetMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array { return ['completed_date'=>['required','date'], 'action_taken'=>['required','string','max:3000'], 'actual_cost'=>['nullable','numeric','min:0','max:9999999999999.99'], 'final_condition'=>['required','in:baik,rusak_ringan,rusak_berat']]; }
    public function messages(): array { return ['completed_date.required'=>'Tanggal selesai wajib diisi.', 'completed_date.date'=>'Tanggal selesai tidak valid.', 'action_taken.required'=>'Tindakan perbaikan wajib diisi.', 'actual_cost.numeric'=>'Biaya aktual harus berupa angka.', 'actual_cost.min'=>'Biaya aktual tidak boleh negatif.', 'final_condition.required'=>'Kondisi akhir wajib dipilih.', 'final_condition.in'=>'Kondisi akhir tidak valid.']; }
}
