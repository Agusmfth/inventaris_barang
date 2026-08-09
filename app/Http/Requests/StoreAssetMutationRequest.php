<?php
namespace App\Http\Requests;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAssetMutationRequest extends FormRequest {
    public function authorize(): bool{return $this->user()?->isAdmin()===true;}
    public function rules(): array{return ['asset_id'=>['required','integer','exists:assets,id'],'to_location_id'=>['required','integer',Rule::exists('asset_locations','id')->where('is_active',true)],'mutation_date'=>['required','date'],'reason'=>['nullable','string','max:255'],'notes'=>['nullable','string','max:2000']];}
    public function messages(): array{return ['asset_id.required'=>'Aset wajib dipilih.','asset_id.exists'=>'Aset yang dipilih tidak ditemukan.','to_location_id.required'=>'Lokasi tujuan wajib dipilih.','to_location_id.exists'=>'Lokasi tujuan tidak valid atau tidak aktif.','mutation_date.required'=>'Tanggal mutasi wajib diisi.','mutation_date.date'=>'Tanggal mutasi tidak valid.','reason.max'=>'Alasan maksimal 255 karakter.','notes.max'=>'Keterangan maksimal 2.000 karakter.'];}
    public function after(): array{return [function($validator){$asset=Asset::find($this->asset_id);if($asset && (int)$asset->location_id===(int)$this->to_location_id)$validator->errors()->add('to_location_id','Lokasi tujuan tidak boleh sama dengan lokasi saat ini.');}];}
}
