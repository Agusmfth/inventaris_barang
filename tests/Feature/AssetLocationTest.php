<?php

namespace Tests\Feature;

use App\Models\AssetLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetLocationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User { return User::factory()->create(['role' => User::ROLE_ADMIN]); }

    public function test_admin_can_create_update_toggle_and_delete_location(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('asset-locations.store'), ['name' => 'Ruang Pengujian', 'code' => 'T001', 'person_in_charge' => 'Petugas Uji', 'description' => 'Lokasi untuk pengujian.', 'is_active' => 1])
            ->assertRedirect(route('asset-locations.index'))->assertSessionHas('success', 'Lokasi berhasil ditambahkan.');
        $location = AssetLocation::where('code', 'T001')->firstOrFail();

        $this->put(route('asset-locations.update', $location), ['name' => 'Ruang Pengujian Edit', 'code' => 'T002', 'person_in_charge' => 'Petugas Baru', 'description' => 'Diperbarui.', 'is_active' => 1])
            ->assertRedirect(route('asset-locations.index'));
        $this->assertDatabaseHas('asset_locations', ['id' => $location->id, 'code' => 'T002', 'name' => 'Ruang Pengujian Edit']);

        $this->patch(route('asset-locations.toggle', $location))->assertSessionHas('success');
        $this->assertFalse($location->fresh()->is_active);
        $this->delete(route('asset-locations.destroy', $location))->assertRedirect(route('asset-locations.index'));
        $this->assertDatabaseMissing('asset_locations', ['id' => $location->id]);
    }

    public function test_search_filter_and_pagination_work_for_locations(): void
    {
        $this->actingAs($this->admin());
        AssetLocation::create(['name' => 'Ruang Pencarian Khusus', 'code' => 'SR001', 'person_in_charge' => 'Penanggung Jawab Khusus', 'is_active' => false]);
        $this->get(route('asset-locations.index', ['search' => 'SR001']))->assertOk()->assertSee('Ruang Pencarian Khusus');
        $this->get(route('asset-locations.index', ['search' => 'Penanggung Jawab Khusus']))->assertOk()->assertSee('Ruang Pencarian Khusus');
        $this->get(route('asset-locations.index', ['status' => 'inactive']))->assertOk()->assertSee('Ruang Pencarian Khusus');

        foreach (range(1, 11) as $number) AssetLocation::create(['name' => sprintf('Ruang Paging %02d', $number), 'code' => 'LP'.str_pad($number, 2, '0', STR_PAD_LEFT)]);
        $this->get(route('asset-locations.index', ['search' => 'Ruang Paging']))->assertOk()->assertSee('page=2', false);
        $this->get(route('asset-locations.index', ['search' => 'Ruang Paging', 'page' => 2]))->assertOk()->assertSee('Ruang Paging 11');
    }

    public function test_location_validation_and_role_authorization_are_enforced(): void
    {
        $admin = $this->admin();
        AssetLocation::create(['name' => 'Lokasi Unik', 'code' => 'LUNIK']);
        $this->actingAs($admin)->post(route('asset-locations.store'), ['name' => 'Lokasi Unik', 'code' => 'LUNIK', 'is_active' => 1])->assertSessionHasErrors(['name', 'code']);

        $headmaster = User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('asset-locations.index'))->assertOk();
        $this->actingAs($headmaster)->post(route('asset-locations.store'), ['name' => 'Dilarang', 'code' => 'NOPE', 'is_active' => 1])->assertForbidden();
    }
}
