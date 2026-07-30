<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Dispute;
use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use App\Betting\Services\MarketStateMachine;
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

    private function createAdmin(): User
    {
        return User::factory()->create([
            'email' => 'admin-betting@test.com',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
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

    private function openMarket(User $creator, BettingEvent $event, array $overrides = []): Market
    {
        $market = app(MarketService::class)->createDraft($creator, $event, array_merge([
            'title' => 'Will Team A win?',
            'description' => 'Friendly wager',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 500,
        ], $overrides));

        return app(MarketService::class)->submitForReview($market, $creator);
    }

    private function accept(Market $market, User $challenger): Market
    {
        return app(MarketService::class)->acceptChallenge($market, $challenger, $market->invite_token);
    }

    public function test_starter_grant_is_idempotent(): void
    {
        $user = $this->createBettingUser('grant');
        $wallet = app(PlayWalletService::class)->getOrCreateWallet($user);

        $this->assertTrue(app(PlayWalletService::class)->grantStarterPoints($user) === false);
        $this->assertEquals(config('betting.starter_points'), (float) $wallet->fresh()->available);
    }

    public function test_open_market_hard_reserves_creator_stake(): void
    {
        $creator = $this->createBettingUser('reserve');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 500]);

        $this->assertEquals(MarketStatus::Open, $market->status);
        $this->assertEquals(500, (float) $creator->bettingWallet->fresh()->locked);
        $this->assertEquals(9500, (float) $creator->bettingWallet->fresh()->available);
    }

    public function test_market_acceptance_locks_stakes_and_versions_terms(): void
    {
        $creator = $this->createBettingUser('c');
        $challenger = $this->createBettingUser('d');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 500]);
        $market = $this->accept($market, $challenger);

        $this->assertEquals(MarketStatus::FullyMatched, $market->status);
        $this->assertNotNull($market->currentVersion);
        $this->assertEquals(1, $market->currentVersion->version);
        $this->assertEquals(500, (float) $creator->bettingWallet->fresh()->locked);
        $this->assertEquals(500, (float) $challenger->bettingWallet->fresh()->locked);
    }

    public function test_accept_requires_invite_token(): void
    {
        $creator = $this->createBettingUser('tok1');
        $challenger = $this->createBettingUser('tok2');
        $event = $this->createEvent();
        $market = $this->openMarket($creator, $event);

        $this->expectException(\RuntimeException::class);
        app(MarketService::class)->acceptChallenge($market, $challenger, 'wrong-token');
    }

    public function test_open_private_market_forbidden_without_invite(): void
    {
        $creator = $this->createBettingUser('priv1');
        $outsider = $this->createBettingUser('priv2');
        $event = $this->createEvent();
        $market = $this->openMarket($creator, $event);

        $response = $this->actingAs($outsider)->get(route('betting.challenges.show', $market));
        $response->assertForbidden();
    }

    public function test_invite_session_allows_view_and_accept(): void
    {
        $creator = $this->createBettingUser('inv1');
        $challenger = $this->createBettingUser('inv2');
        $event = $this->createEvent();
        $market = $this->openMarket($creator, $event, ['stake_amount' => 100]);

        $this->actingAs($challenger)
            ->get(route('betting.invite.show', $market->invite_token))
            ->assertOk();

        $this->actingAs($challenger)
            ->get(route('betting.challenges.show', $market))
            ->assertOk();

        $this->actingAs($challenger)
            ->post(route('betting.challenges.accept', $market), ['invite_token' => $market->invite_token])
            ->assertRedirect();

        $this->assertEquals(MarketStatus::FullyMatched, $market->fresh()->status);
    }

    public function test_cancel_releases_creator_reserve(): void
    {
        $creator = $this->createBettingUser('can1');
        $event = $this->createEvent();
        $market = $this->openMarket($creator, $event, ['stake_amount' => 400]);

        app(MarketService::class)->cancelBeforeMatch($market, $creator);

        $this->assertEquals(MarketStatus::Cancelled, $market->fresh()->status);
        $this->assertEquals(0, (float) $creator->bettingWallet->fresh()->locked);
        $this->assertEquals(10000, (float) $creator->bettingWallet->fresh()->available);
    }

    public function test_settlement_balances_wallets(): void
    {
        $creator = $this->createBettingUser('e');
        $challenger = $this->createBettingUser('f');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Outcome test',
            'stake_amount' => 1000,
        ]);
        $market = $this->accept($market, $challenger);

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

        $market = $this->openMarket($creator, $event, [
            'title' => 'Void test',
            'creator_outcome' => 'No',
            'stake_amount' => 250,
        ]);
        $market = $this->accept($market, $challenger);

        app(SettlementService::class)->voidMarket($market, null, 'test_void');

        $this->assertEquals(MarketStatus::Voided, $market->fresh()->status);
        $this->assertEquals(10000, (float) $creator->bettingWallet->fresh()->available);
        $this->assertEquals(10000, (float) $challenger->bettingWallet->fresh()->available);
    }

    public function test_prohibited_content_flags_manual_review(): void
    {
        $creator = $this->createBettingUser('i');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Bad market about suicide',
            'stake_amount' => 100,
        ]);

        $this->assertEquals(MarketStatus::PendingReview, $market->status);
        $this->assertNotEmpty($market->review_flags);
    }

    public function test_terms_immutable_after_match(): void
    {
        $creator = $this->createBettingUser('j');
        $challenger = $this->createBettingUser('k');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Immutable test',
            'stake_amount' => 100,
        ]);
        $market = $this->accept($market, $challenger);

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

        $market = $this->openMarket($creator, $event, [
            'title' => 'Close test',
            'stake_amount' => 100,
        ]);

        $market->betting_close_at = now()->subMinute();
        $market->save();

        $this->expectException(\RuntimeException::class);
        $this->accept($market->fresh(), $challenger);
    }

    public function test_invalid_winning_outcome_rejected(): void
    {
        $creator = $this->createBettingUser('o');
        $challenger = $this->createBettingUser('p');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Invalid result',
            'stake_amount' => 100,
        ]);
        $market = $this->accept($market, $challenger);

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->publishMarketResult($market, 'Maybe', null);
    }

    public function test_matched_market_forbidden_to_outsider(): void
    {
        $creator = $this->createBettingUser('q');
        $challenger = $this->createBettingUser('r');
        $outsider = $this->createBettingUser('s');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Private test',
            'stake_amount' => 100,
        ]);
        $market = $this->accept($market, $challenger);

        $response = $this->actingAs($outsider)->get(route('betting.challenges.show', $market));
        $response->assertForbidden();
    }

    public function test_settle_blocked_with_open_dispute(): void
    {
        $creator = $this->createBettingUser('t');
        $challenger = $this->createBettingUser('u');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Dispute block',
            'stake_amount' => 100,
        ]);
        $market = $this->accept($market, $challenger);

        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);
        $market = $market->fresh();

        app(SettlementService::class)->openDispute($market, $challenger, 'wrong_result', 'test');

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->settleMarket($market->fresh());
    }

    public function test_settle_blocked_during_active_dispute_window_without_force(): void
    {
        $creator = $this->createBettingUser('win1');
        $challenger = $this->createBettingUser('win2');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 100]);
        $market = $this->accept($market, $challenger);
        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->settleMarket($market->fresh(), force: false);
    }

    public function test_resolve_dispute_closes_all_open_disputes_atomically(): void
    {
        $creator = $this->createBettingUser('dis1');
        $challenger = $this->createBettingUser('dis2');
        $admin = $this->createAdmin();
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 100]);
        $market = $this->accept($market, $challenger);
        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);
        $market = $market->fresh();

        app(SettlementService::class)->openDispute($market, $challenger, 'wrong_result', 'a');
        // Second dispute while under_dispute is not allowed by openDispute status check.
        // Seed a second open dispute to simulate concurrent disputes before status flip.
        Dispute::create([
            'betting_market_id' => $market->id,
            'user_id' => $creator->id,
            'reason_category' => 'other',
            'explanation' => 'b',
            'status' => 'open',
        ]);

        $dispute = Dispute::where('betting_market_id', $market->id)->where('user_id', $challenger->id)->firstOrFail();

        $result = app(SettlementService::class)->resolveDispute($dispute, $admin, 'confirm', 'ok');

        $this->assertEquals(MarketStatus::Settled, $result->status);
        $this->assertEquals(0, Dispute::where('betting_market_id', $market->id)->where('status', 'open')->count());
        $this->assertEquals(2, Dispute::where('betting_market_id', $market->id)->where('status', 'resolved')->count());
    }

    public function test_event_result_rejects_incompatible_market_outcomes(): void
    {
        $creator = $this->createBettingUser('ev1');
        $challenger = $this->createBettingUser('ev2');
        $admin = $this->createAdmin();
        $event = $this->createEvent();

        $yesNo = $this->openMarket($creator, $event, [
            'title' => 'Yes no market',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 100,
        ]);
        $this->accept($yesNo, $challenger);

        $team = app(MarketService::class)->createDraft($challenger, $event, [
            'title' => 'Team market',
            'format' => 'team_vs_team',
            'team_a' => 'Alpha',
            'team_b' => 'Beta',
            'creator_outcome' => 'Alpha',
            'stake_amount' => 100,
        ]);
        $team = app(MarketService::class)->submitForReview($team, $challenger);
        $this->accept($team, $creator);

        $this->expectException(\RuntimeException::class);
        app(SettlementService::class)->publishEventResult($event, 'Yes', $admin);
    }

    public function test_post_match_cancelled_transition_forbidden(): void
    {
        $creator = $this->createBettingUser('sm1');
        $challenger = $this->createBettingUser('sm2');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 100]);
        $market = $this->accept($market, $challenger);

        $this->expectException(\InvalidArgumentException::class);
        app(MarketStateMachine::class)->transition($market, MarketStatus::Cancelled, null, 'bad');
    }

    public function test_ensure_dispute_window_finalizes_on_read(): void
    {
        $creator = $this->createBettingUser('fin1');
        $challenger = $this->createBettingUser('fin2');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, ['stake_amount' => 100]);
        $market = $this->accept($market, $challenger);
        app(SettlementService::class)->publishMarketResult($market, 'Yes', null);
        $market = $market->fresh();
        $market->dispute_window_ends_at = now()->subMinute();
        $market->save();

        $finalized = app(SettlementService::class)->ensureDisputeWindowFinalized($market->fresh());

        $this->assertEquals(MarketStatus::Settled, $finalized->status);
    }

    public function test_advance_events_moves_matched_markets(): void
    {
        $creator = $this->createBettingUser('v');
        $challenger = $this->createBettingUser('w');
        $event = $this->createEvent();

        $market = $this->openMarket($creator, $event, [
            'title' => 'Advance test',
            'stake_amount' => 100,
        ]);
        $market = $this->accept($market, $challenger);

        $event->start_at = now()->subMinute();
        $event->save();

        app(SettlementService::class)->advanceMarketsForEventStart($event);

        $this->assertEquals(MarketStatus::PendingResult, $market->fresh()->status);
    }

    public function test_self_block_and_report_rejected(): void
    {
        $user = $this->createBettingUser('self');

        $this->actingAs($user)
            ->post(route('betting.users.block', $user))
            ->assertRedirect();

        $this->assertDatabaseMissing('betting_user_blocks', [
            'blocker_id' => $user->id,
            'blocked_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('betting.users.report', $user), ['reason' => 'spam'])
            ->assertRedirect();

        $this->assertDatabaseMissing('betting_user_reports', [
            'reporter_id' => $user->id,
            'reported_id' => $user->id,
        ]);
    }

    public function test_wallet_adjust_is_idempotent_for_same_key(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createBettingUser('wadj');
        $key = (string) Str::uuid();

        $this->actingAs($admin)
            ->post(route('admin.betting.wallets.adjust', $user), [
                'amount' => 50,
                'reason' => 'test credit',
                'confirm_reason' => 'test credit',
                'confirm_username' => 'playerwadj',
                'idempotency_key' => $key,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.betting.wallets.adjust', $user), [
                'amount' => 50,
                'reason' => 'test credit',
                'confirm_reason' => 'test credit',
                'confirm_username' => 'playerwadj',
                'idempotency_key' => $key,
            ])
            ->assertRedirect();

        $this->assertEquals(10050, (float) $user->bettingWallet->fresh()->available);
    }

    public function test_verified_user_onboarding_sets_play_only_despite_cached_null_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'onboard@test.com',
            'email_verified_at' => now(),
        ]);

        // Mimic OnboardingController::store accessing bettingProfile before create.
        $this->assertNull($user->bettingProfile);

        $this->actingAs($user)
            ->post(route('betting.onboarding.store'), [
                'username' => 'onboarduser',
                'country' => 'ES',
                'language' => 'en',
                'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
                'accept_terms' => '1',
                'accept_gambling_rules' => '1',
                'accept_privacy' => '1',
                'accept_responsible_gambling' => '1',
                'accept_customer_funds' => '1',
            ])
            ->assertRedirect(route('betting.dashboard'));

        $this->assertEquals(AccountState::PlayOnly, $user->fresh()->bettingProfile->account_state);
        $this->assertEquals(config('betting.starter_points'), (float) $user->fresh()->bettingWallet->available);
    }

    public function test_create_challenge_self_heals_unverified_profile_when_email_verified(): void
    {
        $user = User::factory()->create([
            'email' => 'stuck@test.com',
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'stuckuser',
            'country' => 'ES',
            'language' => 'en',
            'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
            'accept_gambling_rules' => true,
            'accept_privacy' => true,
            'accept_responsible_gambling' => true,
            'accept_customer_funds' => true,
        ]);

        $this->assertEquals(AccountState::Unverified, $user->fresh()->bettingProfile->account_state);

        $this->actingAs($user->fresh())
            ->get(route('betting.challenges.create'))
            ->assertOk();

        $this->assertEquals(AccountState::PlayOnly, $user->fresh()->bettingProfile->account_state);
    }
}
