<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetLoan;
use App\Models\FundingSource;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User { return User::factory()->create(['role'=>User::ROLE_ADMIN]); }
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name'=>'Tablet Operasional','category_id'=>AssetCategory::where('name','Elektronik')->value('id'),'location_id'=>AssetLocation::where('name','Ruang Administrasi')->value('id'),'funding_source_id'=>FundingSource::where('code','BOS')->value('id'),'brand'=>'Samsung','model'=>'Tab A','serial_number'=>'TEST-SERIAL','acquisition_year'=>2027,'acquisition_date'=>'2027-01-10','acquisition_price'=>'7.500.000','quantity'=>2,'condition'=>'baik','description'=>'Aset pengujian.',
        ], $overrides);
    }

    public function test_admin_can_add_asset_with_automatic_code_and_photo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('assets.store'), $this->validData(['photo'=>UploadedFile::fake()->image('tablet.webp',800,600)]))
            ->assertRedirect(route('assets.index'))->assertSessionHas('success','Aset berhasil ditambahkan.');
        $asset = Asset::where('name','Tablet Operasional')->firstOrFail();
        $this->assertSame('AST-2027-0001',$asset->asset_code);
        $this->assertSame('7500000.00',$asset->acquisition_price);
        $this->assertSame('tersedia',$asset->status);
        $this->assertSame($admin->id,$asset->created_by);
        Storage::disk('public')->assertExists($asset->photo);
        $this->get(route('assets.photo', $asset))->assertOk()->assertHeader('content-disposition', 'inline');

        $this->post(route('assets.store'), $this->validData(['name'=>'Tablet Kedua','serial_number'=>'TEST-SERIAL-2']))->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets',['name'=>'Tablet Kedua','asset_code'=>'AST-2027-0002']);
    }

    public function test_asset_detail_is_visible_but_edit_is_admin_only(): void
    {
        $asset = Asset::firstOrFail();
        $headmaster = User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('assets.show',$asset))->assertOk()->assertSee($asset->asset_code)->assertSee('Riwayat Aset');
        $this->get(route('assets.edit',$asset))->assertForbidden();
        $this->put(route('assets.update',$asset), $this->validData())->assertForbidden();
        $this->actingAs($this->admin())->get(route('assets.edit',$asset))->assertOk()->assertSee($asset->asset_code);
        $this->get('/data-aset/999999')->assertNotFound();
    }

    public function test_admin_can_update_asset_preserve_status_and_manage_photo_safely(): void
    {
        Storage::fake('public');
        $asset = Asset::firstOrFail();
        $asset->update(['status'=>'dipinjam','photo'=>'assets/old.jpg']);
        Storage::disk('public')->put('assets/old.jpg','old');
        $this->actingAs($this->admin())->put(route('assets.update',$asset), $this->validData(['name'=>'Aset Diperbarui','status'=>'dihapus']))
            ->assertRedirect(route('assets.show',$asset))->assertSessionHas('success','Aset berhasil diperbarui.');
        $asset->refresh();
        $this->assertSame('dipinjam',$asset->status);
        $this->assertSame('assets/old.jpg',$asset->photo);
        Storage::disk('public')->assertExists('assets/old.jpg');

        $this->put(route('assets.update',$asset), $this->validData(['photo'=>UploadedFile::fake()->image('new.jpg')]))->assertRedirect(route('assets.show',$asset));
        $asset->refresh();
        Storage::disk('public')->assertMissing('assets/old.jpg');
        Storage::disk('public')->assertExists($asset->photo);
    }

    public function test_asset_update_validation_is_indonesian(): void
    {
        $asset = Asset::firstOrFail();
        $this->actingAs($this->admin())->put(route('assets.update',$asset), ['name'=>'','acquisition_price'=>'abc'])
            ->assertSessionHasErrors(['name','category_id','location_id','acquisition_year','acquisition_price','quantity','condition']);
    }

    public function test_asset_quantity_cannot_be_reduced_below_active_borrowed_quantity(): void
    {
        $asset=Asset::where('status','tersedia')->firstOrFail();$asset->update(['quantity'=>10]);$admin=$this->admin();
        AssetLoan::create(['asset_id'=>$asset->id,'borrower_name'=>'Anggota Koperasi','quantity'=>4,'loan_date'=>'2026-08-09','status'=>'dipinjam','created_by'=>$admin->id]);
        $this->actingAs($admin)->put(route('assets.update',$asset),$this->validData(['quantity'=>3]))->assertSessionHasErrors('quantity');
        $this->assertSame(10,$asset->fresh()->quantity);
    }

    public function test_asset_search_filters_pagination_and_master_counts_work(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('assets.index',['search'=>'SN-ASUS-001']))->assertOk()->assertSee('Laptop ASUS X415');
        $heavyAsset = Asset::where('condition','rusak_berat')->first();
        if (! $heavyAsset) { $heavyAsset = Asset::where('name','!=','Laptop ASUS X415')->firstOrFail(); $heavyAsset->update(['condition'=>'rusak_berat']); }
        $this->get(route('assets.index',['condition'=>'rusak_berat']))->assertOk()->assertSee($heavyAsset->name)->assertDontSee('Laptop ASUS X415');
        $this->get(route('assets.index',['status'=>'dipinjam']))->assertOk()->assertSee('Proyektor Epson EB-X06');
        $electronics = AssetCategory::where('name','Elektronik')->firstOrFail();
        $electronicsCount = Asset::where('status','!=','dihapus')->where('category_id',$electronics->id)->sum('quantity');
        $this->get(route('asset-categories.index'))->assertOk()->assertSee($electronicsCount.' Aset');
        $firstLocation = AssetLocation::orderBy('code')->firstOrFail();
        $locationCount = Asset::where('status','!=','dihapus')->where('location_id',$firstLocation->id)->sum('quantity');
        $this->get(route('asset-locations.index'))->assertOk()->assertSee($locationCount.' Aset');
        $activeFundingCount = Asset::where('status','!=','dihapus')->where('funding_source_id',FundingSource::where('code','APBD')->value('id'))->sum('quantity');
        $this->get(route('funding-sources.index'))->assertOk()->assertSee($activeFundingCount.' Aset');

        $category = AssetCategory::where('name','Elektronik')->first(); $location = AssetLocation::first();
        foreach (range(1,11) as $i) Asset::create(['asset_code'=>'AST-2025-'.str_pad((string)(100+$i),4,'0',STR_PAD_LEFT),'name'=>'Aset Paging '.$i,'category_id'=>$category->id,'location_id'=>$location->id,'acquisition_year'=>2025,'quantity'=>1,'condition'=>'baik','status'=>'tersedia']);
        $this->get(route('assets.index',['search'=>'Aset Paging']))->assertOk()->assertSee('page=2',false);
    }

    public function test_dashboard_uses_database_statistics_and_role_is_enforced(): void
    {
        $admin = $this->admin();
        $inventory = Asset::where('status','!=','dihapus');
        $expectedTotal = number_format((clone $inventory)->sum('quantity'),0,',','.');
        $expectedValue = 'Rp '.number_format((float)(clone $inventory)->selectRaw('COALESCE(SUM(acquisition_price * quantity),0) AS total')->value('total'),0,',','.');
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee($expectedTotal)->assertSee($expectedValue)->assertSee('Laptop ASUS X415');
        $headmaster = User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('assets.index'))->assertOk();
        $this->actingAs($headmaster)->get(route('assets.create'))->assertForbidden();
        $this->actingAs($headmaster)->post(route('assets.store'),$this->validData())->assertForbidden();
    }

    public function test_required_asset_fields_are_validated(): void
    {
        $this->actingAs($this->admin())->post(route('assets.store'),[])->assertSessionHasErrors(['name','category_id','location_id','acquisition_year','acquisition_price','quantity','condition']);
    }
}
