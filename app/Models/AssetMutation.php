<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssetMutation extends Model {
    protected $fillable=['asset_id','from_location_id','to_location_id','mutation_date','reason','notes','created_by'];
    protected $casts=['mutation_date'=>'date'];
    public function asset(): BelongsTo{return $this->belongsTo(Asset::class);}
    public function fromLocation(): BelongsTo{return $this->belongsTo(AssetLocation::class,'from_location_id');}
    public function toLocation(): BelongsTo{return $this->belongsTo(AssetLocation::class,'to_location_id');}
    public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
}
