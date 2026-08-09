<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('asset_categories', 'id')->where('is_active', true)],
            'location_id' => ['required', Rule::exists('asset_locations', 'id')->where('is_active', true)],
            'funding_source_id' => ['nullable', Rule::exists('funding_sources', 'id')->where('is_active', true)],
            'brand' => ['nullable', 'string', 'max:100'], 'model' => ['nullable', 'string', 'max:100'], 'serial_number' => ['nullable', 'string', 'max:150'],
            'acquisition_year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'acquisition_date' => ['nullable', 'date'], 'acquisition_price' => ['required', 'numeric', 'min:0', 'max:9999999999999999'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'condition' => ['required', Rule::in(Asset::CONDITIONS)], 'status' => ['nullable', Rule::in(Asset::STATUSES)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return ['name.required'=>'Nama aset wajib diisi.','category_id.required'=>'Kategori aset wajib dipilih.','category_id.exists'=>'Kategori aset tidak valid atau tidak aktif.','location_id.required'=>'Lokasi aset wajib dipilih.','location_id.exists'=>'Lokasi aset tidak valid atau tidak aktif.','funding_source_id.exists'=>'Sumber dana tidak valid atau tidak aktif.','acquisition_year.required'=>'Tahun pengadaan wajib diisi.','acquisition_year.integer'=>'Tahun pengadaan harus berupa angka.','acquisition_price.required'=>'Harga perolehan wajib diisi.','acquisition_price.numeric'=>'Harga perolehan harus berupa angka.','quantity.required'=>'Jumlah aset wajib diisi.','quantity.min'=>'Jumlah minimal 1.','condition.required'=>'Kondisi aset wajib dipilih.','photo.image'=>'Foto barang harus berupa gambar.','photo.mimes'=>'Format foto harus jpg, jpeg, png, atau webp.','photo.max'=>'Ukuran foto maksimal 5 MB.'];
    }
    protected function prepareForValidation(): void
    {
        $this->merge(['acquisition_price' => preg_replace('/\D/', '', (string) $this->acquisition_price), 'status' => $this->status ?: 'tersedia']);
    }
}
