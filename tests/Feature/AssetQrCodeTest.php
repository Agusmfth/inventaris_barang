<?php
namespace Tests\Feature;
use App\Models\{Asset,AssetCategory,AssetLocation,User};
use App\Services\AssetQrCodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
class AssetQrCodeTest extends TestCase
{
    use DatabaseTransactions;
    private function admin():User{return User::factory()->create(['role'=>User::ROLE_ADMIN]);}
    public function test_qr_grid_search_filters_and_preview_are_available():void
    {
        $asset=Asset::with(['category','location'])->firstOrFail();$this->actingAs($this->admin());
        $this->get(route('asset-qr-codes.index',['search'=>$asset->asset_code,'category'=>$asset->category_id,'location'=>$asset->location_id,'condition'=>$asset->condition,'status'=>$asset->status]))->assertOk()->assertSee($asset->asset_code)->assertSee('Lihat QR')->assertSee('data:image/svg+xml;base64',false)->assertSee('Cetak QR Terpilih');
    }
    public function test_png_download_and_print_views_use_school_identity():void
    {
        $asset=Asset::firstOrFail();$this->actingAs($this->admin());
        $download=$this->get(route('asset-qr-codes.download',$asset))->assertOk()->assertDownload('QR-'.$asset->asset_code.'.png')->assertHeader('content-type','image/png');
        $this->assertStringStartsWith("\x89PNG",$download->getContent());
        $this->get(route('asset-qr-codes.print',$asset))->assertOk()->assertSee($asset->asset_code)->assertSee($asset->name)->assertSee('Scan untuk melihat informasi aset');
        $this->post(route('asset-qr-codes.print-selected'),['asset_ids'=>[$asset->id]])->assertOk()->assertSee($asset->asset_code)->assertSee('QR CODE ASET INVENTARIS');
    }
    public function test_label_and_qr_center_share_public_payload_and_deleted_asset_remains_public():void
    {
        $asset=Asset::firstOrFail();$service=app(AssetQrCodeService::class);
        $this->assertSame(route('assets.public-info',['asset'=>$asset->asset_code]),$service->publicUrl($asset));
        $this->actingAs($this->admin())->get(route('asset-labels.single',$asset))->assertOk()->assertSee('data:image/svg+xml;base64',false);
        $asset->update(['status'=>'dihapus']);$this->post(route('logout'));
        $this->get(route('assets.public-info',['asset'=>$asset->asset_code]))->assertOk()->assertSee('Tidak Aktif / Dihapus dari Inventaris')->assertDontSee('Harga')->assertDontSee('created_by');
    }
    public function test_qr_management_is_admin_only_and_selection_is_validated():void
    {
        $this->get(route('asset-qr-codes.index'))->assertRedirect(route('login'));
        $head=User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);$this->actingAs($head)->get(route('asset-qr-codes.index'))->assertForbidden();
        $this->actingAs($this->admin())->post(route('asset-qr-codes.print-selected'),[])->assertSessionHasErrors('asset_ids');
    }
}
