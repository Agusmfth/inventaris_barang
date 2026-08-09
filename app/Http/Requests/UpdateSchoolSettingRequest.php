<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolSettingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array
    {
        return [
            'school_name'=>['required','string','max:255'], 'npsn'=>['nullable','string','max:30'], 'principal_name'=>['nullable','string','max:255'],
            'address'=>['nullable','string','max:1000'], 'village'=>['nullable','string','max:255'], 'district'=>['nullable','string','max:255'],
            'city'=>['nullable','string','max:255'], 'province'=>['nullable','string','max:255'], 'postal_code'=>['nullable','string','max:10'],
            'phone'=>['nullable','string','max:30'], 'email'=>['nullable','email','max:255'], 'website'=>['nullable','url','max:255'],
            'logo'=>['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'], 'inventory_label_title'=>['nullable','string','max:100'],
            'inventory_label_mark'=>['nullable','string','max:60'],
            'inventory_label_footer'=>['nullable','string','max:150'],
        ];
    }
    public function messages(): array
    {
        return ['school_name.required'=>'Nama sekolah wajib diisi.','school_name.max'=>'Nama sekolah maksimal 255 karakter.','email.email'=>'Alamat email tidak valid.','website.url'=>'Alamat website harus berupa URL yang valid.','logo.image'=>'Logo harus berupa gambar.','logo.mimes'=>'Format logo harus PNG, JPG, JPEG, atau WEBP.','logo.max'=>'Ukuran logo maksimal 2 MB.','inventory_label_title.max'=>'Judul label maksimal 100 karakter.','inventory_label_mark.max'=>'Teks identitas label maksimal 60 karakter.','inventory_label_footer.max'=>'Footer label maksimal 150 karakter.'];
    }
}
