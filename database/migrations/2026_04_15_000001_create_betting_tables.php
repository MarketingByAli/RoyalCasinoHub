<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('username', 32)->unique();
            $table->string('display_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('language', 10)->default('en');
            $table->date('date_of_birth');
            $table->string('account_state', 32)->default('unverified');
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamp('gambling_rules_accepted_at')->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->timestamp('marketing_consent_at')->nullable();
            $table->timestamp('responsible_gambling_ack_at')->nullable();
            $table->timestamp('customer_funds_ack_at')->nullable();
            $table->string('referral_code', 32)->nullable()->unique();
            $table->boolean('hide_wager_amounts')->default(false);
            $table->boolean('hide_betting_activity')->default(false);
            $table->timestamps();
        });

        Schema::create('betting_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 16)->default('POINTS');
            $table->decimal('available', 16, 2)->default(0);
            $table->decimal('locked', 16, 2)->default(0);
            $table->boolean('starter_grant_issued')->default(false);
            $table->timestamps();
        });

        Schema::create('betting_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('betting_wallets')->cascadeOnDelete();
            $table->string('type', 32);
            $table->decimal('amount', 16, 2);
            $table->decimal('balance_after_available', 16, 2);
            $table->decimal('balance_after_locked', 16, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('idempotency_key')->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('betting_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 64);
            $table->string('organiser')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('completes_at')->nullable();
            $table->timestamp('betting_close_at')->nullable();
            $table->string('status', 32)->default('scheduled');
            $table->string('settlement_source')->nullable();
            $table->string('winning_outcome')->nullable();
            $table->timestamp('result_published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('betting_event_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_event_id')->constrained('betting_events')->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('external_id')->nullable();
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();
        });

        Schema::create('betting_markets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('betting_event_id')->constrained('betting_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('format', 32);
            $table->json('outcome_options');
            $table->string('creator_outcome');
            $table->decimal('stake_amount', 16, 2);
            $table->string('status', 32)->default('draft');
            $table->string('visibility', 32)->default('private_invite');
            $table->string('invite_token', 64)->unique();
            $table->decimal('platform_fee_percent', 5, 2)->default(0);
            $table->timestamp('betting_close_at')->nullable();
            $table->unsignedSmallInteger('dispute_window_hours')->default(24);
            $table->string('winning_outcome')->nullable();
            $table->json('review_flags')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('dispute_window_ends_at')->nullable();
            $table->foreignId('challenger_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'creator_id']);
            $table->index('invite_token');
        });

        Schema::create('betting_market_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_market_id')->constrained('betting_markets')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('terms_hash', 64);
            $table->json('terms_snapshot');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['betting_market_id', 'version']);
        });

        Schema::table('betting_markets', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('betting_market_versions')->nullOnDelete();
        });

        Schema::create('betting_market_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_market_id')->constrained('betting_markets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->string('outcome');
            $table->decimal('stake_amount', 16, 2);
            $table->foreignId('market_version_id')->nullable()->constrained('betting_market_versions')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['betting_market_id', 'user_id']);
        });

        Schema::create('betting_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_market_id')->constrained('betting_markets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason_category', 64);
            $table->text('explanation')->nullable();
            $table->string('status', 32)->default('open');
            $table->string('resolution', 32)->nullable();
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('betting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 32)->default('user');
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('betting_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['follower_id', 'following_id']);
        });

        Schema::create('betting_user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['blocker_id', 'blocked_id']);
        });

        Schema::create('betting_user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 64);
            $table->text('explanation')->nullable();
            $table->string('status', 32)->default('open');
            $table->timestamps();
        });

        Schema::create('betting_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('betting_markets', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });

        Schema::dropIfExists('betting_notifications');
        Schema::dropIfExists('betting_user_reports');
        Schema::dropIfExists('betting_user_blocks');
        Schema::dropIfExists('betting_followers');
        Schema::dropIfExists('betting_audit_logs');
        Schema::dropIfExists('betting_disputes');
        Schema::dropIfExists('betting_market_participants');
        Schema::dropIfExists('betting_market_versions');
        Schema::dropIfExists('betting_markets');
        Schema::dropIfExists('betting_event_sources');
        Schema::dropIfExists('betting_events');
        Schema::dropIfExists('betting_ledger_entries');
        Schema::dropIfExists('betting_wallets');
        Schema::dropIfExists('user_profiles');
    }
};
