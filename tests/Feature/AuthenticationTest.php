<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee('Selamat Datang Kembali');
    }

    public function test_invalid_credentials_return_an_error(): void
    {
        $this->from('/login')->post('/login', ['username' => 'tidak-ada', 'password' => 'salah'])
            ->assertRedirect('/login')->assertSessionHasErrors('login');
    }

    public function test_active_admin_can_login(): void
    {
        $user = User::factory()->create(['username' => 'admin-test', 'password' => Hash::make('rahasia')]);
        $this->post('/login', ['username' => 'admin-test', 'password' => 'rahasia'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create(['username' => 'nonaktif', 'password' => Hash::make('rahasia'), 'is_active' => false]);
        $this->post('/login', ['username' => 'nonaktif', 'password' => 'rahasia'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_animation_endpoint_returns_json_feedback(): void
    {
        $this->postJson('/login', ['username'=>'tidak-ada','password'=>'salah'])
            ->assertUnprocessable()->assertJson(['message'=>'Username atau password yang Anda masukkan salah.']);

        $user=User::factory()->create(['username'=>'animasi-login','password'=>Hash::make('rahasia')]);
        $this->postJson('/login', ['username'=>'animasi-login','password'=>'rahasia'])
            ->assertOk()->assertJsonPath('redirect',route('dashboard'))->assertJsonPath('message','Selamat datang, '.$user->name.'.');
        $this->assertAuthenticatedAs($user);
    }
}
