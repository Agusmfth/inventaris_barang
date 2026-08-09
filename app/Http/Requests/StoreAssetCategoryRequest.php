<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_categories', 'name')],
            'code' => ['nullable', 'string', 'max:10', Rule::unique('asset_categories', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Nama kategori wajib diisi.', 'name.unique' => 'Nama kategori sudah digunakan.', 'code.max' => 'Kode kategori terlalu panjang.', 'code.unique' => 'Kode kategori sudah digunakan.', 'description.max' => 'Deskripsi maksimal 1.000 karakter.'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => $this->filled('code') ? strtoupper(trim($this->code)) : null, 'is_active' => $this->boolean('is_active')]);
    }
}
