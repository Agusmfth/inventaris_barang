<?php
namespace App\Http\Requests;
use App\Models\User;use Illuminate\Foundation\Http\FormRequest;
class RejectAssetDisposalRequest extends FormRequest{public function authorize():bool{return in_array($this->user()?->role,[User::ROLE_ADMIN,User::ROLE_KEPALA_SEKOLAH],true);}public function rules():array{return ['rejection_reason'=>['required','string','max:3000']];}public function messages():array{return ['rejection_reason.required'=>'Alasan penolakan wajib diisi.'];}}
