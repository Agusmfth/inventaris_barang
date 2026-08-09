<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    protected $fillable=['asset_id','disposal_date','reason','disposal_method','condition_at_disposal','notes','document_number','document_file','created_by','approved_by','status','approved_at','rejection_reason'];
    protected $casts=['disposal_date'=>'date','approved_at'=>'datetime'];
    public function asset():BelongsTo{return $this->belongsTo(Asset::class);}
    public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
    public function approver():BelongsTo{return $this->belongsTo(User::class,'approved_by');}
    public function getStatusLabelAttribute():string{return match($this->status){'diajukan'=>'Menunggu Persetujuan','disetujui'=>'Disetujui','ditolak'=>'Ditolak'};}
    public function getMethodLabelAttribute():string{return match($this->disposal_method){'pemusnahan'=>'Pemusnahan','penjualan'=>'Penjualan / Lelang','hibah'=>'Hibah Keluar','hilang'=>'Hilang','lainnya'=>'Lainnya'};}
    public function getConditionLabelAttribute():string{return match($this->condition_at_disposal){'baik'=>'Baik','rusak_ringan'=>'Rusak Ringan','rusak_berat'=>'Rusak Berat'};}
}
