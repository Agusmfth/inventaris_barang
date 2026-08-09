<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QualityPolishTest extends TestCase
{
    use DatabaseTransactions;

    public function test_professional_error_pages_are_available(): void
    {
        $this->get('/alamat-yang-tidak-ada')->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')->assertSee('Kembali ke Dashboard');

        $headmaster = User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($headmaster)->get(route('assets.create'))->assertForbidden()
            ->assertSee('Akses tidak diizinkan')->assertDontSee('Stack trace');

        $this->assertStringContainsString('Sesi telah berakhir', view('errors.419')->render());
        $this->assertStringContainsString('Terjadi kendala pada sistem', view('errors.500')->render());
    }

    public function test_main_navigation_has_no_dead_links_for_each_role(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_KEPALA_SEKOLAH] as $role) {
            $user = User::factory()->create(['role'=>$role]);
            $this->actingAs($user);
            foreach (config('navigation.pages') as $slug=>$page) {
                if (!in_array($role,$page['roles'],true)) continue;
                $url = isset($page['route']) ? route($page['route']) : route('placeholder',$slug);
                $this->get($url)->assertOk();
            }
        }
    }
}
