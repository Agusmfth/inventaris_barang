<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreAssetDisposalRequest extends FormRequest{
public function authorize():bool{return $this->user()?->isAdmin()===true;}
public function rules():array{return ['asset_id'=>['required','integer','exists:assets,id'],'disposal_date'=>['required','date'],'reason'=>['required','string','max:3000'],'disposal_method'=>['required','in:pemusnahan,penjualan,hibah,hilang,lainnya'],'condition_at_disposal'=>['required','in:baik,rusak_ringan,rusak_berat'],'document_number'=>['nullable','string','max:255'],'document_file'=>['nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],'notes'=>['nullable','string','max:3000']];}
public function messages():array{return ['asset_id.required'=>'Aset wajib dipilih.','disposal_date.required'=>'Tanggal penghapusan wajib diisi.','disposal_date.date'=>'Tanggal penghapusan tidak valid.','reason.required'=>'Alasan penghapusan wajib diisi.','disposal_method.required'=>'Metode penghapusan wajib dipilih.','disposal_method.in'=>'Metode penghapusan tidak valid.','condition_at_disposal.required'=>'Kondisi aset wajib dipilih.','document_file.mimes'=>'Dokumen harus berupa PDF, JPG, JPEG, atau PNG.','document_file.max'=>'Ukuran dokumen maksimal 5 MB.'];}}
