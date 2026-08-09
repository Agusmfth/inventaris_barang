<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetScannerController extends Controller
{
    public function index(): View
    {
        return view('asset-scanner.index');
    }

    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:2048'],
            'mode' => ['required', 'in:scan,manual'],
        ], [
            'value.required' => 'QR Code atau kode aset wajib diisi.',
            'value.max' => 'Data QR Code terlalu panjang.',
            'mode.required' => 'Jenis pencarian tidak valid.',
            'mode.in' => 'Jenis pencarian tidak valid.',
        ]);

        $assetCode = $validated['mode'] === 'manual'
            ? $this->manualCode($validated['value'])
            : $this->codeFromOwnedUrl($validated['value'], $request);

        if ($assetCode === null) {
            return response()->json([
                'status' => 'invalid',
                'title' => 'QR Code tidak dikenali',
                'message' => 'QR yang dipindai bukan berasal dari Sistem Inventaris Sekolah.',
            ], 422);
        }

        $asset = Asset::where('asset_code', $assetCode)->first();
        if (! $asset) {
            return response()->json([
                'status' => 'not_found',
                'title' => 'Data aset tidak ditemukan',
                'message' => 'QR valid, tetapi data aset tidak tersedia di sistem.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'name' => $asset->name,
            'asset_code' => $asset->asset_code,
            'redirect_url' => route('assets.show', $asset),
        ]);
    }

    private function manualCode(string $value): ?string
    {
        $code = strtoupper(trim($value));
        return preg_match('/^AST-\d{4}-\d{4,}$/', $code) ? $code : null;
    }

    private function codeFromOwnedUrl(string $value, Request $request): ?string
    {
        $parts = parse_url(trim($value));
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'], $parts['path'])) return null;
        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) return null;

        $allowedHosts = array_filter([
            strtolower($request->getHost()),
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
        ]);
        if (! in_array(strtolower($parts['host']), array_unique($allowedHosts), true)) return null;
        if (! preg_match('#/aset/(AST-\d{4}-\d{4,})/info/?$#i', $parts['path'], $matches)) return null;

        return strtoupper($matches[1]);
    }
}
