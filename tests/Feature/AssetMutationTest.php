<?php
namespace Tests\Feature;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetLoan;
use App\Models\AssetMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class AssetMutationTest extends TestCase
{
    use DatabaseTransactions;
    private function admin(): User { return User::factory()->create(['role'=>User::ROLE_ADMIN]); }
    private function destination(Asset $asset): AssetLocation { return AssetLocation::where('is_active',true)->whereKeyNot($asset->location_id)->firstOrFail(); }

    public function test_admin_can_mutate_asset_and_history_is_stored_atomically(): void
    {
        $asset=Asset::firstOrFail(); $old=$asset->location_id; $to=$this->destination($asset); $condition=$asset->condition; $status=$asset->status;
        $response=$this->actingAs($this->admin())->post(route('asset-mutations.store'),['asset_id'=>$asset->id,'to_location_id'=>$to->id,'mutation_date'=>'2026-08-10','reason'=>'Kebutuhan administrasi','notes'=>'Dipindahkan dengan aman.']);
        $mutation=AssetMutation::latest('id')->firstOrFail();
        $response->assertRedirect(route('asset-mutations.show',$mutation))->assertSessionHas('success','Mutasi aset berhasil disimpan.');
        $asset->refresh(); $this->assertSame($to->id,$asset->location_id); $this->assertSame($condition,$asset->condition); $this->assertSame($status,$asset->status);
        $this->assertDatabaseHas('asset_mutations',['asset_id'=>$asset->id,'from_location_id'=>$old,'to_location_id'=>$to->id]);
        $this->get(route('assets.show',$asset))->assertOk()->assertSee('Mutasi Aset')->assertSee($to->name);
        $this->get(route('asset-mutations.show',$mutation))->assertOk()->assertSee($asset->asset_code)->assertSee('Kebutuhan administrasi');
    }

    public function test_same_location_and_required_fields_are_rejected_in_indonesian(): void
    {
        $asset=Asset::firstOrFail();
        $this->actingAs($this->admin())->post(route('asset-mutations.store'),['asset_id'=>$asset->id,'to_location_id'=>$asset->location_id,'mutation_date'=>'2026-08-10'])->assertSessionHasErrors(['to_location_id']);
        $this->assertDatabaseMissing('asset_mutations',['asset_id'=>$asset->id]);
        $this->post(route('asset-mutations.store'),[])->assertSessionHasErrors(['asset_id','to_location_id','mutation_date']);
    }

    public function test_partially_borrowed_asset_is_hidden_and_cannot_be_mutated(): void
    {
        $asset=Asset::where('status','tersedia')->firstOrFail();$asset->update(['quantity'=>10]);$admin=$this->admin();$destination=$this->destination($asset);
        AssetLoan::create(['asset_id'=>$asset->id,'borrower_name'=>'Anggota Koperasi','quantity'=>2,'loan_date'=>'2026-08-09','status'=>'dipinjam','created_by'=>$admin->id]);
        $this->actingAs($admin)->get(route('asset-mutations.index'))->assertOk()->assertDontSee($asset->asset_code);
        $this->post(route('asset-mutations.store'),['asset_id'=>$asset->id,'to_location_id'=>$destination->id,'mutation_date'=>'2026-08-10'])->assertSessionHasErrors('asset_id');
        $this->assertSame($asset->location_id,$asset->fresh()->location_id);
    }

    public function test_failed_asset_update_rolls_back_mutation(): void
    {
        $asset=Asset::firstOrFail(); $old=$asset->location_id; $to=$this->destination($asset);
        Event::listen('eloquent.updating: '.Asset::class,fn()=>throw new RuntimeException('Simulasi gagal'));
        try { $this->actingAs($this->admin())->post(route('asset-mutations.store'),['asset_id'=>$asset->id,'to_location_id'=>$to->id,'mutation_date'=>'2026-08-10']); } catch(RuntimeException $e) { $this->assertSame('Simulasi gagal',$e->getMessage()); }
        $this->assertDatabaseMissing('asset_mutations',['asset_id'=>$asset->id,'to_location_id'=>$to->id]);
        $this->assertSame($old,$asset->fresh()->location_id);
    }

    public function test_headmaster_can_view_but_cannot_create_mutation(): void
    {
        $asset=Asset::firstOrFail(); $head=User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($head)->get(route('asset-mutations.index'))->assertOk()->assertDontSee('data-bs-target="#mutationModal"',false);
        $this->post(route('asset-mutations.store'),['asset_id'=>$asset->id,'to_location_id'=>$this->destination($asset)->id,'mutation_date'=>'2026-08-10'])->assertForbidden();
    }
}
