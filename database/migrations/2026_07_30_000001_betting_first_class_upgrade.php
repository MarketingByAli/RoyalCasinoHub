<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('betting_markets', function (Blueprint $table) {
            $table->unsignedSmallInteger('participant_cap')->default(2)->after('stake_amount');
            $table->unsignedSmallInteger('min_participants')->default(2)->after('participant_cap');
        });

        Schema::table('betting_market_participants', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->after('role');
            $table->decimal('proposed_stake_amount', 16, 2)->nullable()->after('stake_amount');
            $table->string('proposed_outcome')->nullable()->after('proposed_stake_amount');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('referred_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->timestamp('referral_credited_at')->nullable()->after('referred_by_user_id');
        });

        Schema::create('betting_rg_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('daily_stake_limit', 16, 2)->nullable();
            $table->decimal('weekly_stake_limit', 16, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('betting_rg_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'type', 'ends_at']);
        });

        Schema::create('betting_dispute_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_dispute_id')->constrained('betting_disputes')->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });

        Schema::create('betting_leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('period', 16);
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->decimal('net_points', 16, 2)->default(0);
            $table->unsignedInteger('settled_markets')->default(0);
            $table->timestamps();
            $table->unique(['period', 'period_start', 'user_id']);
            $table->index(['period', 'period_start', 'rank']);
        });

        Schema::create('betting_faucet_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2);
            $table->timestamp('claimed_at');
            $table->timestamps();
            $table->index(['user_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betting_faucet_claims');
        Schema::dropIfExists('betting_leaderboard_snapshots');
        Schema::dropIfExists('betting_dispute_attachments');
        Schema::dropIfExists('betting_rg_actions');
        Schema::dropIfExists('betting_rg_limits');

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn('referral_credited_at');
        });

        Schema::table('betting_market_participants', function (Blueprint $table) {
            $table->dropColumn(['status', 'proposed_stake_amount', 'proposed_outcome']);
        });

        Schema::table('betting_markets', function (Blueprint $table) {
            $table->dropColumn(['participant_cap', 'min_participants']);
        });
    }
};
