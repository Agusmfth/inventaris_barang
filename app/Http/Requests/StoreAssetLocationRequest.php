<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetLocationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_locations', 'name')],
            'code' => ['required', 'string', 'max:20', Rule::unique('asset_locations', 'code')],
            'person_in_charge' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return ['name.required' => 'Nama lokasi wajib diisi.', 'name.unique' => 'Nama lokasi sudah digunakan.', 'code.required' => 'Kode lokasi wajib diisi.', 'code.unique' => 'Kode lokasi sudah digunakan.', 'code.max' => 'Kode lokasi maksimal 20 karakter.', 'person_in_charge.max' => 'Nama penanggung jawab maksimal 255 karakter.', 'description.max' => 'Deskripsi maksimal 1.000 karakter.'];
    }
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->code)), 'is_active' => $this->boolean('is_active')]);
    }
}
