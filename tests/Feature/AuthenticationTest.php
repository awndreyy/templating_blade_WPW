<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_kasir_dashboard(): void
    {
        $response = $this->get(route('kasir.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'testadmin@minimarket.test'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_kasir_dashboard(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'testadmin@minimarket.test'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $response = $this->actingAs($admin)->get(route('kasir.dashboard'));

        $response->assertStatus(403);
    }

    public function test_kasir_can_access_kasir_dashboard(): void
    {
        $kasir = User::firstOrCreate(
            ['email' => 'testkasir@minimarket.test'],
            ['name' => 'Test Kasir', 'password' => bcrypt('password'), 'role' => 'kasir']
        );

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));

        $response->assertStatus(200);
    }

    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        $kasir = User::firstOrCreate(
            ['email' => 'testkasir@minimarket.test'],
            ['name' => 'Test Kasir', 'password' => bcrypt('password'), 'role' => 'kasir']
        );

        $response = $this->actingAs($kasir)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_user_can_login_with_valid_credentials_and_redirects_based_on_role(): void
    {
        User::firstOrCreate(
            ['email' => 'testadmin@minimarket.test'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $response = $this->post(route('login'), [
            'email' => 'testadmin@minimarket.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_user_can_logout(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'testadmin@minimarket.test'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $response = $this->actingAs($admin)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
