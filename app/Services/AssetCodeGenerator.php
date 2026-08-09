<?php

namespace App\Services;

use App\Models\Asset;

class AssetCodeGenerator
{
    public function generate(int $year): string
    {
        $prefix = "AST-{$year}-";
        $lastCode = Asset::where('asset_code', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('asset_code')->value('asset_code');
        $sequence = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;
        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
