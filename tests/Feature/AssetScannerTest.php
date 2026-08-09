<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetScannerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_roles_can_open_scanner_but_guest_cannot(): void
    {
        $this->get(route('asset-scanner.index'))->assertRedirect(route('login'));

        foreach ([User::ROLE_ADMIN, User::ROLE_KEPALA_SEKOLAH] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('asset-scanner.index'))
                ->assertOk()->assertSee('Scan QR Aset')->assertSee('Mulai Kamera')->assertSee('Masukkan Kode Aset');
        }
    }

    public function test_existing_public_qr_resolves_to_internal_asset_detail(): void
    {
        $asset = Asset::firstOrFail();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)->postJson(route('asset-scanner.resolve'), [
            'value' => route('assets.public-info', ['asset' => $asset->asset_code]),
            'mode' => 'scan',
        ])->assertOk()->assertJson([
            'status' => 'success',
            'name' => $asset->name,
            'asset_code' => $asset->asset_code,
            'redirect_url' => route('assets.show', $asset),
        ]);
    }

    public function test_external_and_malformed_qr_values_are_rejected_without_redirect(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        foreach (['https://example.com/aset/AST-2026-0001/info', 'javascript:alert(1)', 'AST-2026-0001'] as $value) {
            $this->actingAs($user)->postJson(route('asset-scanner.resolve'), ['value' => $value, 'mode' => 'scan'])
                ->assertStatus(422)->assertJson(['status' => 'invalid', 'title' => 'QR Code tidak dikenali'])->assertJsonMissing(['redirect_url']);
        }
    }

    public function test_valid_missing_asset_and_manual_code_have_clear_results(): void
    {
        $asset = Asset::firstOrFail();
        $user = User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]);

        $this->actingAs($user)->postJson(route('asset-scanner.resolve'), [
            'value' => url('/aset/AST-2099-9999/info'), 'mode' => 'scan',
        ])->assertNotFound()->assertJson(['status' => 'not_found', 'title' => 'Data aset tidak ditemukan']);

        $this->postJson(route('asset-scanner.resolve'), [
            'value' => strtolower($asset->asset_code), 'mode' => 'manual',
        ])->assertOk()->assertJson(['status' => 'success', 'asset_code' => $asset->asset_code]);

        $this->postJson(route('asset-scanner.resolve'), [
            'value' => 'kode bebas', 'mode' => 'manual',
        ])->assertStatus(422)->assertJson(['status' => 'invalid']);
    }
}
