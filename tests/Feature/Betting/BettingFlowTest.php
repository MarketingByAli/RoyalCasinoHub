<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\SettlementService;
use App\Betting\Services\UserProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BettingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createBettingUser(string $suffix = 'a'): User
    {
        $user = User::factory()->create([
            'email' => "player{$suffix}@test.com",
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'player'.$suffix,
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
            'title' => 'Test Match',
            'slug' => 'test-match-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'betting_close_at' => now()->addHours(20),
            'status' => 'scheduled',
            'settlement_source' => 'Official league',
        ]);
    }

    public function test_starter_grant_is_idempotent(): void
    {
        $user = $this->createBettingUser('grant');
        $wallet = app(PlayWalletService::class)->getOrCreateWallet($user);

        $this->assertTrue(app(PlayWalletService::class)->grantStarterPoints($user) === false);
        $this->assertEquals(config('betting.starter_points'), (float) $wallet->fresh()->available);
    }

    public function test_market_acceptance_locks_stakes_and_versions_terms(): void
    {
        $creator = $this->createBettingUser('c');
        $challenger = $this->createBettingUser('d');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Will Team A win?',
            'description' => 'Friendly wager',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 500,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $this->assertEquals(MarketStatus::Open, $market->status);

        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        $this->assertEquals(MarketStatus::FullyMatched, $market->status);
        $this->assertNotNull($market->currentVersion);
        $this->assertEquals(1, $market->currentVersion->version);
        $this->assertEquals(500, (float) $creator->bettingWallet->fresh()->locked);
        $this->assertEquals(500, (float) $challenger->bettingWallet->fresh()->locked);
    }

    public function test_settlement_balances_wallets(): void
    {
        $creator = $this->createBettingUser('e');
        $challenger = $this->createBettingUser('f');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Outcome test',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 1000,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);
        $market->refresh();

        $market->dispute_window_ends_at = now()->subMinute();
        $market->save();

        app(SettlementService::class)->finalizeAfterDisputeWindow($market->fresh());

        $creatorWallet = $creator->bettingWallet->fresh();
        $challengerWallet = $challenger->bettingWallet->fresh();

        $this->assertEquals(MarketStatus::Settled, $market->fresh()->status);
        $this->assertEquals(11000, (float) $creatorWallet->available);
        $this->assertEquals(0, (float) $creatorWallet->locked);
        $this->assertEquals(9000, (float) $challengerWallet->available);
        $this->assertEquals(0, (float) $challengerWallet->locked);
    }

    public function test_void_refunds_locked_stakes(): void
    {
        $creator = $this->createBettingUser('g');
        $challenger = $this->createBettingUser('h');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Void test',
            'format' => 'yes_no',
            'creator_outcome' => 'No',
            'stake_amount' => 250,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        app(SettlementService::class)->voidMarket($market, null, 'test_void');

        $this->assertEquals(MarketStatus::Voided, $market->fresh()->status);
        $this->assertEquals(10000, (float) $creator->bettingWallet->fresh()->available);
        $this->assertEquals(10000, (float) $challenger->bettingWallet->fresh()->available);
    }

    public function test_prohibited_content_flags_manual_review(): void
    {
        $creator = $this->createBettingUser('i');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Bad market about suicide',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $this->assertEquals(MarketStatus::PendingReview, $market->status);
        $this->assertNotEmpty($market->review_flags);
    }

    public function test_terms_immutable_after_match(): void
    {
        $creator = $this->createBettingUser('j');
        $challenger = $this->createBettingUser('k');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Immutable test',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        $hash = $market->currentVersion->terms_hash;
        $market->title = 'Changed title';
        $market->save();

        $this->assertEquals($hash, $market->fresh()->currentVersion->terms_hash);
        $this->assertEquals('Immutable test', $market->currentVersion->terms_snapshot['title']);
    }

    public function test_betting_close_blocks_acceptance(): void
    {
        $creator = $this->createBettingUser('m');
        $challenger = $this->createBettingUser('n');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Close test',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);

        $market->betting_close_at = now()->subMinute();
        $market->save();

        $this->expectException(\RuntimeException::class);
        app(MarketService::class)->acceptChallenge($market->fresh(), $challenger);
    }

    public function test_invalid_winning_outcome_rejected(): void
    {
        $creator = $this->createBettingUser('o');
        $challenger = $this->createBettingUser('p');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Invalid result',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->publishMarketResult($market, 'Maybe', null);
    }

    public function test_matched_market_forbidden_to_outsider(): void
    {
        $creator = $this->createBettingUser('q');
        $challenger = $this->createBettingUser('r');
        $outsider = $this->createBettingUser('s');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Private test',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        $response = $this->actingAs($outsider)->get(route('betting.challenges.show', $market));
        $response->assertForbidden();
    }

    public function test_settle_blocked_with_open_dispute(): void
    {
        $creator = $this->createBettingUser('t');
        $challenger = $this->createBettingUser('u');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Dispute block',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);
        $market = $market->fresh();

        app(SettlementService::class)->openDispute($market, $challenger, 'wrong_result', 'test');

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->settleMarket($market->fresh());
    }

    public function test_advance_events_moves_matched_markets(): void
    {
        $creator = $this->createBettingUser('v');
        $challenger = $this->createBettingUser('w');
        $event = $this->createEvent();

        $market = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Advance test',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $market = app(MarketService::class)->submitForReview($market, $creator);
        $market = app(MarketService::class)->acceptChallenge($market, $challenger);

        $event->start_at = now()->subMinute();
        $event->save();

        app(SettlementService::class)->advanceMarketsForEventStart($event);

        $this->assertEquals(MarketStatus::PendingResult, $market->fresh()->status);
    }
}
