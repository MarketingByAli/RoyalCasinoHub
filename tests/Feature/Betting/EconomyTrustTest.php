<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Wallet;
use App\Betting\Services\FaucetService;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\ResponsibleGamblingService;
use App\Betting\Services\SettlementReversalService;
use App\Betting\Services\SettlementService;
use App\Betting\Services\UserProfileService;
use App\Betting\Services\WalletReconciliationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EconomyTrustTest extends TestCase
{
    use RefreshDatabase;

    private function createBettingUser(string $suffix): User
    {
        $user = User::factory()->create([
            'email' => "eco{$suffix}@test.com",
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'eco'.$suffix,
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

    public function test_faucet_cooldown(): void
    {
        $user = $this->createBettingUser('f');
        app(FaucetService::class)->claim($user);
        $this->assertFalse(app(FaucetService::class)->canClaim($user->fresh()));

        $this->expectException(\RuntimeException::class);
        app(FaucetService::class)->claim($user->fresh());
    }

    public function test_platform_fee_credited_to_house(): void
    {
        $house = $this->createBettingUser('house');
        config(['betting.house_user_id' => $house->id, 'betting.platform_fee_percent' => 10]);

        $creator = $this->createBettingUser('fc');
        $challenger = $this->createBettingUser('fd');
        $admin = User::factory()->create(['email' => 'admfee@test.com', 'email_verified_at' => now(), 'role' => 'admin']);
        $event = BettingEvent::create([
            'title' => 'Fee Event',
            'slug' => 'fee-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Fee market',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 1000,
        ]);
        // Force fee on market even if create used config after set.
        $market->platform_fee_percent = 10;
        $market->save();
        $market = app(MarketService::class)->submitForReview($market->fresh(), $creator);
        app(MarketService::class)->acceptChallenge($market->fresh(), $challenger, $market->invite_token);

        app(SettlementService::class)->publishMarketResult($market->fresh(), 'Yes', $admin);
        $market = $market->fresh();
        $market->dispute_window_ends_at = now()->subMinute();
        $market->save();
        app(SettlementService::class)->settleMarket($market->fresh(), force: true);

        // Pot 1000, fee 10% = 100 to house
        $this->assertEquals(100.0, (float) $house->bettingWallet->fresh()->available - 10000);
    }

    public function test_reconcile_detects_mismatch(): void
    {
        $user = $this->createBettingUser('rec');
        $wallet = $user->bettingWallet;
        $wallet->available = (float) $wallet->available + 50;
        $wallet->save();

        $mismatches = app(WalletReconciliationService::class)->findMismatches();
        $this->assertTrue($mismatches->contains(fn ($row) => $row['user_id'] === $user->id));
    }

    public function test_self_exclude_blocks_create(): void
    {
        $user = $this->createBettingUser('rg');
        app(ResponsibleGamblingService::class)->startSelfExclusion($user);

        $event = BettingEvent::create([
            'title' => 'RG Event',
            'slug' => 'rg-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $this->expectException(\RuntimeException::class);
        app(MarketService::class)->createDraft($user->fresh(), $event, [
            'title' => 'Blocked',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
    }

    public function test_settlement_reversal_restores_under_dispute(): void
    {
        $creator = $this->createBettingUser('rv1');
        $challenger = $this->createBettingUser('rv2');
        $admin = User::factory()->create(['email' => 'admrev@test.com', 'email_verified_at' => now(), 'role' => 'admin']);
        $event = BettingEvent::create([
            'title' => 'Rev Event',
            'slug' => 'rev-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Rev market',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 500,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        app(MarketService::class)->acceptChallenge($market->fresh(), $challenger, $market->invite_token);
        app(SettlementService::class)->publishMarketResult($market->fresh(), 'Yes', $admin);
        $market = $market->fresh();
        $market->dispute_window_ends_at = now()->subMinute();
        $market->save();
        app(SettlementService::class)->settleMarket($market->fresh(), force: true);

        $reversed = app(SettlementReversalService::class)->reverseSettlement($market->fresh(), $admin, 'bad settle');
        $this->assertEquals(MarketStatus::UnderDispute, $reversed->status);
    }

    public function test_moderation_flags_links(): void
    {
        $review = app(\App\Betting\Services\MarketModerationService::class)->review(
            'See https://spam.example',
            'click www.evil.com',
            ['Yes', 'No']
        );

        $this->assertTrue($review['requires_manual_review']);
        $this->assertContains('link_spam', $review['flags']);
    }
}
