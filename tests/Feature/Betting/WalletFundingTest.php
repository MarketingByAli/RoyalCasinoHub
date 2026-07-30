<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Models\DepositMethod;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\UserProfileService;
use App\Betting\Services\WalletFundingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WalletFundingTest extends TestCase
{
    use RefreshDatabase;

    private function createBettingUser(string $suffix): User
    {
        $user = User::factory()->create([
            'email' => "fund{$suffix}@test.com",
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'fund'.$suffix,
            'country' => 'ES',
            'language' => 'en',
            'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
            'accept_gambling_rules' => true,
            'accept_privacy' => true,
            'accept_responsible_gambling' => true,
            'accept_customer_funds' => true,
        ]);
        $user->fresh()->bettingProfile->update(['account_state' => AccountState::PlayOnly]);
        app(PlayWalletService::class)->grantStarterPoints($user);

        return $user->fresh();
    }

    public function test_admin_can_create_deposit_method_with_qr(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'email' => 'admin-fund@test.com',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.betting.deposit-methods.store'), [
                'coin_name' => 'USDT',
                'network' => 'TRC20',
                'address' => 'TXyzExampleAddress123',
                'instructions' => 'Send only USDT on TRC20.',
                'sort_order' => 1,
                'is_active' => '1',
                'qr_code' => UploadedFile::fake()->image('qr.png', 200, 200),
            ])
            ->assertRedirect(route('admin.betting.deposit-methods.index'));

        $method = DepositMethod::first();
        $this->assertNotNull($method);
        $this->assertEquals('USDT', $method->coin_name);
        $this->assertNotNull($method->qr_path);
        Storage::disk('public')->assertExists($method->qr_path);
    }

    public function test_wallet_page_shows_deposit_method_without_faucet_copy(): void
    {
        $user = $this->createBettingUser('u');
        DepositMethod::create([
            'coin_name' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qexample',
            'instructions' => 'Bitcoin network only',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('betting.wallet'))
            ->assertOk()
            ->assertSee('Add funds')
            ->assertSee('Withdraw')
            ->assertSee('bc1qexample')
            ->assertDontSee('Faucet')
            ->assertDontSee('faucet');
    }

    public function test_withdraw_and_deposit_notice_flow(): void
    {
        $user = $this->createBettingUser('w');
        $admin = User::factory()->create([
            'email' => 'admin-w@test.com',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        $method = DepositMethod::create([
            'coin_name' => 'USDT',
            'network' => 'TRC20',
            'address' => 'TAdminWallet',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('betting.wallet.withdraw'), [
                'deposit_method_id' => $method->id,
                'destination_address' => 'TUserWallet',
                'amount' => 500,
            ])
            ->assertRedirect();

        $this->assertEquals(9500.0, (float) $user->bettingWallet->fresh()->available);
        $this->assertEquals(500.0, (float) $user->bettingWallet->fresh()->locked);

        $withdraw = \App\Betting\Models\WithdrawRequest::first();
        app(WalletFundingService::class)->approveWithdraw($withdraw, $admin, 'paid tx123');
        $this->assertEquals(9500.0, (float) $user->bettingWallet->fresh()->available);
        $this->assertEquals(0.0, (float) $user->bettingWallet->fresh()->locked);

        $this->actingAs($user)
            ->post(route('betting.wallet.deposit-notice'), [
                'deposit_method_id' => $method->id,
                'amount' => 100,
                'tx_hash' => '0xabc',
            ])
            ->assertRedirect();

        $notice = \App\Betting\Models\DepositNotice::first();
        app(WalletFundingService::class)->creditDepositNotice($notice, $admin, 100);
        $this->assertEquals(9600.0, (float) $user->bettingWallet->fresh()->available);
    }
}
