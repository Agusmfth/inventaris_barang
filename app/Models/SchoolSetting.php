<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = ['school_name','npsn','address','village','district','city','province','postal_code','phone','email','website','principal_name','logo','inventory_label_title','inventory_label_mark','inventory_label_footer'];

    public static function fallback(): self
    {
        return new self(['school_name'=>'Nama Sekolah','inventory_label_title'=>'BARANG INVENTARIS','inventory_label_mark'=>'INVENTARIS SEKOLAH','inventory_label_footer'=>'JAGA & GUNAKAN DENGAN BAIK']);
    }

    public function getDisplayNameAttribute(): string { return $this->school_name ?: 'Nama Sekolah'; }
    public function getLabelTitleAttribute(): string { return $this->inventory_label_title ?: 'BARANG INVENTARIS'; }
    public function getLabelMarkAttribute(): string { return $this->inventory_label_mark ?: 'INVENTARIS SEKOLAH'; }
    public function getLabelFooterAttribute(): string { return $this->inventory_label_footer ?: 'JAGA & GUNAKAN DENGAN BAIK'; }
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo || !\Illuminate\Support\Facades\Storage::disk('public')->exists($this->logo)) return null;
        return route('school-settings.logo');
    }
}
