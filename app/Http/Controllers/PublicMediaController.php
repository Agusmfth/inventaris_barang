<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class PublicMediaController extends Controller
{
    public function __invoke(string $path)
    {
        abort_if(str_contains($path, '..') || !preg_match('#^(assets|school)/[A-Za-z0-9._/-]+$#', $path), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline',
        ]);
    }
}
