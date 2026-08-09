<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_open_admin_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));

        foreach (array_keys(config('navigation.pages')) as $page) {
            $this->get('/halaman/'.$page)->assertOk();
        }
    }

    public function test_headmaster_can_view_assets_and_reports(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($user)->get('/halaman/data-aset')->assertOk();
        $this->actingAs($user)->get('/halaman/laporan-aset')->assertOk();
    }

    public function test_headmaster_cannot_open_admin_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_KEPALA_SEKOLAH]))
            ->get('/halaman/pengguna')->assertForbidden();
    }
}
