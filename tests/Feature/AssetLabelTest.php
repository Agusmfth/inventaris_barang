<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetLabelTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User { return User::factory()->create(['role'=>User::ROLE_ADMIN]); }

    public function test_admin_can_open_label_selection_and_filter_assets(): void
    {
        $this->actingAs($this->admin())->get(route('asset-labels.index'))->assertOk()->assertSee('Cetak Label Inventaris')->assertSee('Pilih semua aset');
        $asset = Asset::where('status','!=','dihapus')->firstOrFail();
        $this->get(route('asset-labels.index',['search'=>$asset->asset_code]))->assertOk()->assertSee($asset->name);
    }

    public function test_single_label_preview_contains_qr_public_url_and_all_sizes_work(): void
    {
        $asset = Asset::firstOrFail();
        foreach (['small','medium','large'] as $size) {
            $this->actingAs($this->admin())->get(route('asset-labels.single',[$asset,'size'=>$size]))->assertOk()->assertSee('label-size-'.$size,false)->assertSee($asset->asset_code);
        }
        $response = $this->get(route('asset-labels.single',$asset));
        $response->assertSee('data:image/svg+xml;base64',false);
        $svg = base64_decode(explode('base64,',$response->getContent())[1] ?? '');
        $this->assertStringContainsString('svg', $svg);
    }

    public function test_single_preview_can_print_more_than_one_unit(): void
    {
        $asset = Asset::where('quantity','>',1)->firstOrFail();
        $this->actingAs($this->admin())->get(route('asset-labels.single',[$asset,'quantity'=>3]))
            ->assertOk()->assertSee('Preview 3 Label')->assertSee('UNIT 03/')->assertSee('Jumlah label');
        $this->get(route('asset-labels.single',[$asset,'quantity'=>$asset->quantity + 1]))->assertSessionHasErrors('quantity');
    }

    public function test_multiple_labels_respect_requested_quantity_and_server_maximum(): void
    {
        $asset = Asset::where('quantity','>',1)->firstOrFail();
        $payload = ['asset_ids'=>[$asset->id],'quantities'=>[$asset->id=>2],'size'=>'medium'];
        $this->actingAs($this->admin())->post(route('asset-labels.preview'),$payload)->assertOk()->assertSee('Preview 2 Label')->assertSee('UNIT 01/')->assertSee('UNIT 02/');
        $payload['quantities'][$asset->id] = $asset->quantity + 1;
        $this->post(route('asset-labels.preview'),$payload)->assertRedirect()->assertSessionHasErrors('quantities.'.$asset->id);
    }

    public function test_public_asset_info_works_without_login_and_hides_sensitive_fields(): void
    {
        $asset = Asset::firstOrFail();
        $this->get(route('assets.public-info',['asset'=>$asset->asset_code]))->assertOk()->assertSee($asset->asset_code)->assertSee($asset->name)->assertDontSee('Harga Perolehan')->assertDontSee('Nomor Seri');
        $this->get('/aset/KODE-TIDAK-ADA/info')->assertNotFound();
    }

    public function test_label_management_is_admin_only(): void
    {
        $asset = Asset::firstOrFail();
        $headmaster = User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('asset-labels.index'))->assertForbidden();
        $this->get(route('asset-labels.single',$asset))->assertForbidden();
        $this->post(route('asset-labels.preview'),[])->assertForbidden();
    }
}
