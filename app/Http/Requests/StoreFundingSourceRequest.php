<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundingSourceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', Rule::unique('funding_sources')], 'code' => ['required', 'string', 'max:20', Rule::unique('funding_sources')], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['required', 'boolean']];
    }
    public function messages(): array
    {
        return ['name.required' => 'Nama sumber dana wajib diisi.', 'name.unique' => 'Nama sumber dana sudah digunakan.', 'code.required' => 'Kode sumber dana wajib diisi.', 'code.unique' => 'Kode sumber dana sudah digunakan.', 'code.max' => 'Kode sumber dana maksimal 20 karakter.', 'description.max' => 'Deskripsi maksimal 1.000 karakter.'];
    }
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->code)), 'is_active' => $this->boolean('is_active')]);
    }
}
