<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetLocation extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'person_in_charge', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function assets(): HasMany { return $this->hasMany(Asset::class, 'location_id'); }
}
