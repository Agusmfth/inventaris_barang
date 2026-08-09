<?php

namespace Tests\Feature;

use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetCategoryTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_admin_can_create_update_toggle_and_delete_category(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('asset-categories.store'), ['name' => 'Perangkat Uji', 'code' => 'UJI', 'description' => 'Kategori untuk pengujian.', 'is_active' => '1'])
            ->assertRedirect(route('asset-categories.index'))->assertSessionHas('success', 'Kategori aset berhasil ditambahkan.');
        $category = AssetCategory::where('name', 'Perangkat Uji')->firstOrFail();
        $this->assertTrue($category->is_active);

        $this->put(route('asset-categories.update', $category), ['name' => 'Perangkat Uji Edit', 'code' => 'UJIE', 'description' => 'Diperbarui.', 'is_active' => '1'])
            ->assertRedirect(route('asset-categories.index'));
        $this->assertDatabaseHas('asset_categories', ['id' => $category->id, 'name' => 'Perangkat Uji Edit', 'code' => 'UJIE']);

        $this->patch(route('asset-categories.toggle', $category))->assertSessionHas('success');
        $this->assertFalse($category->fresh()->is_active);

        $this->delete(route('asset-categories.destroy', $category))->assertRedirect(route('asset-categories.index'));
        $this->assertDatabaseMissing('asset_categories', ['id' => $category->id]);
    }

    public function test_search_filter_and_pagination_work(): void
    {
        $this->actingAs($this->admin());
        AssetCategory::create(['name' => 'Kategori Pencarian Khusus', 'code' => 'SRCH', 'is_active' => false]);

        $this->get(route('asset-categories.index', ['search' => 'SRCH']))->assertOk()->assertSee('Kategori Pencarian Khusus');
        $this->get(route('asset-categories.index', ['status' => 'inactive']))->assertOk()->assertSee('Kategori Pencarian Khusus');

        foreach (range(1, 11) as $number) AssetCategory::create(['name' => sprintf('Kategori Paging %02d', $number), 'code' => 'PG'.str_pad($number, 2, '0', STR_PAD_LEFT)]);
        $this->get(route('asset-categories.index', ['search' => 'Kategori Paging']))->assertOk()->assertSee('page=2', false);
        $this->get(route('asset-categories.index', ['search' => 'Kategori Paging', 'page' => 2]))->assertOk()->assertSee('Kategori Paging 11');
    }

    public function test_validation_and_role_authorization_are_enforced(): void
    {
        $admin = $this->admin();
        AssetCategory::create(['name' => 'Kategori Unik', 'code' => 'UNIK']);
        $this->actingAs($admin)->post(route('asset-categories.store'), ['name' => 'Kategori Unik', 'code' => 'KODE-TERLALU-PANJANG', 'is_active' => 1])
            ->assertSessionHasErrors(['name', 'code']);

        $headmaster = User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('asset-categories.index'))->assertOk();
        $this->actingAs($headmaster)->post(route('asset-categories.store'), ['name' => 'Dilarang', 'is_active' => 1])->assertForbidden();
    }
}
