<?php
namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() === true; }
    public function rules(): array
    {
        $asset = $this->route('asset');
        $master = fn (string $table, ?int $current) => Rule::exists($table, 'id')->where(fn ($query) => $query->where(fn ($q) => $q->where('is_active', true)->when($current, fn ($n) => $n->orWhere('id', $current))));
        return [
            'name'=>['required','string','max:255'], 'category_id'=>['required',$master('asset_categories',$asset->category_id)],
            'location_id'=>['required',$master('asset_locations',$asset->location_id)], 'funding_source_id'=>['nullable',$master('funding_sources',$asset->funding_source_id)],
            'brand'=>['nullable','string','max:100'], 'model'=>['nullable','string','max:100'], 'serial_number'=>['nullable','string','max:150'],
            'acquisition_year'=>['required','integer','min:1900','max:'.(now()->year + 1)], 'acquisition_date'=>['nullable','date'],
            'acquisition_price'=>['required','numeric','min:0','max:9999999999999999'], 'quantity'=>['required','integer','min:1','max:100000'],
            'condition'=>['required',Rule::in(Asset::CONDITIONS)], 'photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'], 'description'=>['nullable','string','max:2000'],
        ];
    }
    public function messages(): array
    {
        return array_merge((new StoreAssetRequest)->messages(), [
            'name.string'=>'Nama aset harus berupa teks.','name.max'=>'Nama aset maksimal 255 karakter.',
            'brand.max'=>'Merk maksimal 100 karakter.','model.max'=>'Model atau tipe maksimal 100 karakter.','serial_number.max'=>'Nomor seri maksimal 150 karakter.',
            'acquisition_year.min'=>'Tahun pengadaan minimal 1900.','acquisition_year.max'=>'Tahun pengadaan tidak boleh melebihi tahun depan.',
            'acquisition_date.date'=>'Tanggal pengadaan tidak valid.','acquisition_price.min'=>'Harga perolehan tidak boleh kurang dari 0.','acquisition_price.max'=>'Harga perolehan terlalu besar.',
            'quantity.integer'=>'Jumlah aset harus berupa angka bulat.','quantity.max'=>'Jumlah aset maksimal 100.000.','condition.in'=>'Kondisi aset tidak valid.',
            'description.max'=>'Keterangan maksimal 2.000 karakter.',
        ]);
    }
    public function after(): array
    {
        return [function ($validator) {
            $asset = $this->route('asset');
            if (!$asset || !$this->filled('quantity')) return;
            $borrowed = (int) $asset->loans()->whereNull('returned_at')->sum('quantity');
            if ((int) $this->quantity < $borrowed) $validator->errors()->add('quantity', "Jumlah aset tidak boleh kurang dari {$borrowed} unit yang masih dipinjam.");
        }];
    }
    protected function prepareForValidation(): void { $this->merge(['acquisition_price'=>preg_replace('/\D/','',(string)$this->acquisition_price)]); }
}
