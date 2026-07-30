<?php

namespace Tests\Feature\Betting;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Services\LeaderboardService;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\ReferralService;
use App\Betting\Services\UserProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReferralExploreTest extends TestCase
{
    use RefreshDatabase;

    private function createBettingUser(string $suffix): User
    {
        $user = User::factory()->create([
            'email' => "ref{$suffix}@test.com",
            'email_verified_at' => now(),
        ]);

        app(UserProfileService::class)->createForUser($user, [
            'username' => 'ref'.$suffix,
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

    public function test_referral_bonus_credited_once(): void
    {
        $referrer = $this->createBettingUser('r');
        $code = $referrer->bettingProfile->referral_code;

        $referee = User::factory()->create([
            'email' => 'newref@test.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($referee)->post(route('betting.onboarding.store'), [
            'username' => 'newrefuser',
            'country' => 'ES',
            'language' => 'en',
            'date_of_birth' => now()->subYears(22)->format('Y-m-d'),
            'accept_terms' => '1',
            'accept_gambling_rules' => '1',
            'accept_privacy' => '1',
            'accept_responsible_gambling' => '1',
            'accept_customer_funds' => '1',
            'referral_code' => $code,
        ])->assertRedirect();

        $bonus = (float) config('betting.referral_bonus_points', 250);
        $this->assertEquals(10000 + $bonus, (float) $referee->fresh()->bettingWallet->available);
        $this->assertEquals(10000 + $bonus, (float) $referrer->fresh()->bettingWallet->available);

        app(ReferralService::class)->creditIfEligible($referee->fresh());
        $this->assertEquals(10000 + $bonus, (float) $referee->fresh()->bettingWallet->available);
    }

    public function test_public_market_listed_private_not(): void
    {
        $creator = $this->createBettingUser('exp');
        $event = BettingEvent::create([
            'title' => 'Explore Event',
            'slug' => 'explore-'.Str::random(6),
            'category' => 'sport',
            'start_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $public = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Public one',
            'format' => 'yes_no',
            'creator_outcome' => 'Yes',
            'stake_amount' => 50,
            'visibility' => 'public',
        ]);
        app(MarketService::class)->submitForReview($public, $creator);

        $private = app(MarketService::class)->createDraft($creator, $event, [
            'title' => 'Private one',
            'format' => 'yes_no',
            'creator_outcome' => 'No',
            'stake_amount' => 50,
            'visibility' => 'private_invite',
        ]);
        app(MarketService::class)->submitForReview($private, $creator);

        $this->actingAs($creator)
            ->get(route('betting.explore.markets'))
            ->assertOk()
            ->assertSee('Public one')
            ->assertDontSee('Private one');
    }

    public function test_leaderboard_snapshot_command_runs(): void
    {
        $rows = app(LeaderboardService::class)->snapshotWeekly();
        $this->assertCount(0, $rows);
        $this->artisan('betting:snapshot-leaderboard')->assertSuccessful();
    }

    public function test_unread_notification_count_endpoint(): void
    {
        $user = $this->createBettingUser('n');
        \App\Betting\Models\BettingNotification::create([
            'user_id' => $user->id,
            'type' => 'bet_accepted',
            'data' => ['market_title' => 'x'],
        ]);

        $this->actingAs($user)
            ->getJson(route('betting.notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }
}
