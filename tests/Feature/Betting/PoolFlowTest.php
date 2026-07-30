<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Services\MarketMatchingService;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\SettlementService;
use App\Betting\Services\UserProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PoolFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createBettingUser(string $suffix): User
    {
        $user = User::factory()->create([
            'email' => "pool{$suffix}@test.com",
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'pool'.$suffix,
            'country' => 'ES',
            'language' => 'en',
            'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
            'accept_gambling_rules' => true,
            'accept_privacy' => true,
            'accept_responsible_gambling' => true,
            'accept_customer_funds' => true,
        ]);

        $profile = $user->fresh()->bettingProfile;
        $profile->account_state = AccountState::PlayOnly;
        $profile->save();
        app(PlayWalletService::class)->grantStarterPoints($user);

        return $user->fresh();
    }

    private function createEvent(): BettingEvent
    {
        return BettingEvent::create([
            'title' => 'Pool Match',
            'slug' => 'pool-match-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'betting_close_at' => now()->addHours(20),
            'status' => 'scheduled',
            'settlement_source' => 'Official',
        ]);
    }

    public function test_three_seat_public_pool_fills_to_fully_matched(): void
    {
        $creator = $this->createBettingUser('c');
        $a = $this->createBettingUser('a');
        $b = $this->createBettingUser('b');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Pool challenge',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 200,
            'participant_cap' => 3,
            'visibility' => 'public',
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $matching = app(MarketMatchingService::class);
        $market = $matching->join($market, $a, 'No');
        $this->assertEquals(MarketStatus::PartiallyMatched, $market->status);

        $market = $matching->join($market->fresh(), $b, 'No');
        $this->assertEquals(MarketStatus::FullyMatched, $market->status);
        $this->assertEquals(3, $market->participants()->where('status', 'active')->count());
    }

    public function test_private_pool_requires_invite_token(): void
    {
        $creator = $this->createBettingUser('pc');
        $joiner = $this->createBettingUser('pj');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Private pool',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
            'participant_cap' => 3,
            'visibility' => 'private_invite',
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $this->expectException(\RuntimeException::class);
        app(MarketMatchingService::class)->join($market, $joiner, 'No', null);
    }

    public function test_withdraw_releases_joiner_stake(): void
    {
        $creator = $this->createBettingUser('wc');
        $joiner = $this->createBettingUser('wj');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Withdraw pool',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 300,
            'participant_cap' => 4,
            'visibility' => 'public',
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $matching = app(MarketMatchingService::class);
        $matching->join($market, $joiner, 'No');
        $this->assertEquals(300.0, (float) $joiner->bettingWallet->fresh()->locked);

        $matching->withdraw($market->fresh(), $joiner);
        $this->assertEquals(0.0, (float) $joiner->bettingWallet->fresh()->locked);
        $this->assertEquals(MarketStatus::Open, $market->fresh()->status);
    }

    public function test_counter_offer_accept_locks_proposed_stake(): void
    {
        $creator = $this->createBettingUser('cc');
        $joiner = $this->createBettingUser('cj');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Counter pool',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
            'participant_cap' => 2,
            'visibility' => 'public',
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $matching = app(MarketMatchingService::class);
        $matching->join($market, $joiner, 'No', null, 250);
        $this->assertEquals(0.0, (float) $joiner->bettingWallet->fresh()->locked);

        $market = $matching->acceptCounterOffer($market->fresh(), $creator, $joiner);
        $this->assertEquals(MarketStatus::FullyMatched, $market->status);
        $this->assertEquals(250.0, (float) $joiner->bettingWallet->fresh()->locked);
    }

    public function test_pool_settlement_splits_pot(): void
    {
        $creator = $this->createBettingUser('sc');
        $a = $this->createBettingUser('sa');
        $b = $this->createBettingUser('sb');
        $admin = User::factory()->create(['email' => 'admin-pool@test.com', 'email_verified_at' => now(), 'role' => 'admin']);
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Settle pool',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
            'participant_cap' => 3,
            'visibility' => 'public',
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $matching = app(MarketMatchingService::class);
        $matching->join($market, $a, 'No');
        $market = $matching->join($market->fresh(), $b, 'No');

        app(SettlementService::class)->publishMarketResult($market, 'Yes', $admin);
        $market = $market->fresh();
        $market->dispute_window_ends_at = now()->subMinute();
        $market->save();
        app(SettlementService::class)->settleMarket($market->fresh(), force: true);

        // Creator wins; pot 200 from two losers; fee 0 → payout 300
        $this->assertEquals(10200.0, (float) $creator->bettingWallet->fresh()->available);
        $this->assertEquals(9900.0, (float) $a->bettingWallet->fresh()->available);
        $this->assertEquals(9900.0, (float) $b->bettingWallet->fresh()->available);
    }
}
