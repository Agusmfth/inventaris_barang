<?php

namespace Tests\Feature;

use App\Models\FundingSource;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FundingSourceTest extends TestCase
{
    use DatabaseTransactions;
    private function admin(): User { return User::factory()->create(['role' => User::ROLE_ADMIN]); }

    public function test_admin_can_create_update_toggle_and_delete_funding_source(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('funding-sources.store'), ['name' => 'Dana Pengujian', 'code' => 'UJI', 'description' => 'Dana untuk pengujian.', 'is_active' => 1])->assertRedirect(route('funding-sources.index'))->assertSessionHas('success', 'Sumber dana berhasil ditambahkan.');
        $source = FundingSource::where('code', 'UJI')->firstOrFail();
        $this->put(route('funding-sources.update', $source), ['name' => 'Dana Pengujian Edit', 'code' => 'UJIE', 'description' => 'Diperbarui.', 'is_active' => 1])->assertRedirect(route('funding-sources.index'));
        $this->assertDatabaseHas('funding_sources', ['id' => $source->id, 'name' => 'Dana Pengujian Edit', 'code' => 'UJIE']);
        $this->patch(route('funding-sources.toggle', $source))->assertSessionHas('success');
        $this->assertFalse($source->fresh()->is_active);
        $this->delete(route('funding-sources.destroy', $source))->assertRedirect(route('funding-sources.index'));
        $this->assertDatabaseMissing('funding_sources', ['id' => $source->id]);
    }

    public function test_search_filter_and_pagination_work_for_funding_sources(): void
    {
        $this->actingAs($this->admin());
        FundingSource::create(['name' => 'Dana Pencarian Khusus', 'code' => 'SRCX', 'is_active' => false]);
        $this->get(route('funding-sources.index', ['search' => 'SRCX']))->assertOk()->assertSee('Dana Pencarian Khusus');
        $this->get(route('funding-sources.index', ['status' => 'inactive']))->assertOk()->assertSee('Dana Pencarian Khusus');
        foreach (range(1, 11) as $number) FundingSource::create(['name' => sprintf('Dana Paging %02d', $number), 'code' => 'FD'.str_pad($number, 2, '0', STR_PAD_LEFT)]);
        $this->get(route('funding-sources.index', ['search' => 'Dana Paging']))->assertOk()->assertSee('page=2', false);
        $this->get(route('funding-sources.index', ['search' => 'Dana Paging', 'page' => 2]))->assertOk()->assertSee('Dana Paging 11');
    }

    public function test_validation_and_role_authorization_are_enforced(): void
    {
        $admin = $this->admin();
        FundingSource::create(['name' => 'Dana Unik', 'code' => 'DUNIK']);
        $this->actingAs($admin)->post(route('funding-sources.store'), ['name' => 'Dana Unik', 'code' => 'DUNIK', 'is_active' => 1])->assertSessionHasErrors(['name', 'code']);
        $headmaster = User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('funding-sources.index'))->assertOk();
        $this->actingAs($headmaster)->post(route('funding-sources.store'), ['name' => 'Dilarang', 'code' => 'NOPE', 'is_active' => 1])->assertForbidden();
    }
}
