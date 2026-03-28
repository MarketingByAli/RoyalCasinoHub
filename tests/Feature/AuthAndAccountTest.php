<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads(): void
    {
        $response = $this->get(route('password.request'));
        $response->assertOk();
        $response->assertSee('Forgot password', false);
    }

    public function test_terms_and_privacy_pages_load(): void
    {
        $this->get(route('terms'))->assertOk()->assertSee('Terms of use', false);
        $this->get(route('privacy'))->assertOk()->assertSee('Privacy policy', false);
    }

    public function test_guest_cannot_access_account_dashboard(): void
    {
        $this->get(route('account.index'))->assertRedirect(route('login'));
    }

    public function test_verified_user_can_access_account(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('account.index'))->assertOk();
    }

    public function test_unverified_user_redirected_from_account(): void
    {
        $user = User::factory()->unverified()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('account.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_admin_users_page_requires_admin(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'role' => 'user']);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }
}
