<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetMaintenanceTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User { return User::factory()->create(['role'=>User::ROLE_ADMIN]); }
    private function asset(): Asset { $asset=Asset::where('status','tersedia')->firstOrFail();$asset->update(['condition'=>'rusak_ringan']);return $asset; }
    private function data(Asset $asset, array $overrides=[]): array { return array_merge(['asset_id'=>$asset->id, 'reported_date'=>'2026-08-10', 'issue'=>'Tinta tidak keluar', 'initial_condition'=>'rusak_ringan', 'maintenance_status'=>'menunggu', 'service_location'=>'CV Maju Jaya', 'technician'=>'Andi', 'estimated_cost'=>150000, 'start_date'=>null, 'notes'=>'Perlu pemeriksaan menyeluruh.'],$overrides); }

    public function test_report_can_be_created_and_asset_enters_maintenance(): void
    {
        $asset=$this->asset();
        $this->actingAs($this->admin())->post(route('asset-maintenances.store'),$this->data($asset))->assertRedirect();
        $maintenance=AssetMaintenance::latest('id')->firstOrFail();
        $this->assertSame('menunggu',$maintenance->maintenance_status);
        $this->assertSame('perawatan',$asset->fresh()->status);
        $this->assertEquals('150000.00',$maintenance->estimated_cost);
        $this->get(route('asset-maintenances.show',$maintenance))->assertOk()->assertSee('Tinta tidak keluar');
    }

    public function test_borrowed_deleted_and_actively_maintained_assets_are_rejected(): void
    {
        $this->actingAs($this->admin());
        foreach(['dipinjam','dihapus'] as $status){$asset=$this->asset();$asset->update(['status'=>$status]);$this->post(route('asset-maintenances.store'),$this->data($asset))->assertSessionHasErrors('asset_id');}
        $asset=$this->asset();$this->post(route('asset-maintenances.store'),$this->data($asset));
        $this->post(route('asset-maintenances.store'),$this->data($asset))->assertSessionHasErrors('asset_id');
    }

    public function test_partially_borrowed_asset_cannot_enter_maintenance(): void
    {
        $asset=$this->asset();$asset->update(['quantity'=>10,'status'=>'tersedia']);$admin=$this->admin();
        AssetLoan::create(['asset_id'=>$asset->id,'borrower_name'=>'Anggota Koperasi','quantity'=>2,'loan_date'=>'2026-08-09','status'=>'dipinjam','created_by'=>$admin->id]);
        $this->actingAs($admin)->get(route('asset-maintenances.index'))->assertOk()->assertDontSee($asset->asset_code);
        $this->post(route('asset-maintenances.store'),$this->data($asset))->assertSessionHasErrors('asset_id');
        $this->assertSame('tersedia',$asset->fresh()->status);
    }

    public function test_good_asset_is_not_available_and_is_rejected_by_server(): void
    {
        $asset=Asset::where('status','tersedia')->firstOrFail();$asset->update(['condition'=>'baik']);
        $this->actingAs($this->admin())->get(route('asset-maintenances.index'))->assertOk()->assertDontSee($asset->asset_code);
        $this->post(route('asset-maintenances.store'),$this->data($asset,['initial_condition'=>'baik']))->assertSessionHasErrors('asset_id');
        $this->assertSame('tersedia',$asset->fresh()->status);
    }

    public function test_orphan_maintenance_status_can_be_reconciled_by_creating_a_record(): void
    {
        $asset=$this->asset();$asset->update(['status'=>'perawatan','condition'=>'rusak_ringan']);
        $this->assertFalse($asset->maintenances()->whereIn('maintenance_status',AssetMaintenance::ACTIVE_STATUSES)->exists());
        $this->actingAs($this->admin())->get(route('asset-maintenances.index'))->assertOk()->assertSee($asset->asset_code);
        $this->post(route('asset-maintenances.store'),$this->data($asset))->assertRedirect();
        $this->assertDatabaseHas('asset_maintenances',['asset_id'=>$asset->id,'maintenance_status'=>'menunggu']);
    }

    public function test_maintenance_can_start_and_complete_with_asset_condition_and_cost_updated(): void
    {
        $asset=$this->asset();$this->actingAs($this->admin())->post(route('asset-maintenances.store'),$this->data($asset));$maintenance=AssetMaintenance::latest('id')->firstOrFail();
        $this->patch(route('asset-maintenances.start',$maintenance))->assertRedirect(route('asset-maintenances.show',$maintenance));
        $this->assertSame('diproses',$maintenance->fresh()->maintenance_status);$this->assertNotNull($maintenance->fresh()->start_date);$this->assertNotNull($maintenance->fresh()->started_at);$this->assertNotNull($maintenance->fresh()->started_by);
        $this->patch(route('asset-maintenances.complete',$maintenance),['completed_date'=>'2026-08-12','action_taken'=>'Penggantian head printer','actual_cost'=>150000,'final_condition'=>'baik','notes'=>'Sudah diuji.'])->assertRedirect(route('asset-maintenances.show',$maintenance));
        $maintenance->refresh();$asset->refresh();
        $this->assertSame('selesai',$maintenance->maintenance_status);$this->assertSame('150000.00',$maintenance->actual_cost);$this->assertSame('Perlu pemeriksaan menyeluruh.',$maintenance->notes);$this->assertSame('baik',$asset->condition);$this->assertSame('tersedia',$asset->status);$this->assertNotNull($maintenance->completed_by);
        $this->get(route('asset-maintenances.show',$maintenance))->assertSee('Track Record Perawatan')->assertSee('Perawatan mulai diproses')->assertSee('Perawatan diselesaikan');
        $this->get(route('assets.show',$asset))->assertSee('Perawatan Selesai')->assertSee('Penggantian head printer')->assertSee('150.000');
        $this->get(route('dashboard'))->assertOk()->assertSee('Perawatan '.$asset->name.' selesai.');
    }

    public function test_waiting_maintenance_can_be_cancelled(): void
    {
        $asset=$this->asset();$this->actingAs($this->admin())->post(route('asset-maintenances.store'),$this->data($asset));$maintenance=AssetMaintenance::latest('id')->firstOrFail();
        $this->patch(route('asset-maintenances.cancel',$maintenance))->assertRedirect(route('asset-maintenances.show',$maintenance));
        $this->assertSame('dibatalkan',$maintenance->fresh()->maintenance_status);$this->assertNotNull($maintenance->fresh()->cancelled_at);$this->assertNotNull($maintenance->fresh()->cancelled_by);$this->assertSame('tersedia',$asset->fresh()->status);
    }

    public function test_headmaster_is_read_only_and_validation_is_indonesian(): void
    {
        $asset=$this->asset();$admin=$this->admin();$this->actingAs($admin)->post(route('asset-maintenances.store'),$this->data($asset));$maintenance=AssetMaintenance::latest('id')->firstOrFail();
        $head=User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);$this->actingAs($head)->get(route('asset-maintenances.index'))->assertOk()->assertDontSee('Tambah Perawatan');$this->get(route('asset-maintenances.show',$maintenance))->assertOk()->assertDontSee('Mulai Proses');
        $this->post(route('asset-maintenances.store'),[])->assertForbidden();$this->patch(route('asset-maintenances.start',$maintenance))->assertForbidden();$this->patch(route('asset-maintenances.cancel',$maintenance))->assertForbidden();
        $newAsset=Asset::where('status','tersedia')->whereKeyNot($asset->id)->firstOrFail();$this->actingAs($admin)->post(route('asset-maintenances.store'),$this->data($newAsset,['issue'=>'']))->assertSessionHasErrors('issue')->assertSessionHas('errors',fn($errors)=>$errors->first('issue')==='Keluhan atau kerusakan wajib diisi.');
    }
}
