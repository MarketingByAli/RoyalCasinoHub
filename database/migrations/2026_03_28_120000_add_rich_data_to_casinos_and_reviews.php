<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casinos', function (Blueprint $table) {
            $table->string('license_authority_slug')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('support_channels')->nullable();
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();
            $table->decimal('min_deposit', 12, 2)->nullable();
            $table->string('withdrawal_time_text')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedTinyInteger('profile_completeness')->default(0);
            $table->json('gallery_urls')->nullable();
            $table->string('tier')->default('standard');
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('website_last_checked_at')->nullable();
            $table->boolean('website_link_broken')->default(false);
            $table->text('enrichment_last_error')->nullable();
            $table->decimal('rating_avg_trust', 3, 2)->nullable();
            $table->decimal('rating_avg_games', 3, 2)->nullable();
            $table->decimal('rating_avg_support', 3, 2)->nullable();
            $table->decimal('rating_avg_payments', 3, 2)->nullable();
            $table->decimal('rating_avg_bonuses', 3, 2)->nullable();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->json('dimension_ratings')->nullable();
            $table->text('admin_internal_note')->nullable();
            $table->unsignedInteger('helpful_up_count')->default(0);
            $table->unsignedInteger('helpful_down_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'dimension_ratings', 'admin_internal_note', 'helpful_up_count', 'helpful_down_count',
            ]);
        });

        Schema::table('casinos', function (Blueprint $table) {
            $table->dropColumn([
                'license_authority_slug', 'payment_methods', 'support_channels', 'pros', 'cons',
                'min_deposit', 'withdrawal_time_text', 'last_verified_at', 'profile_completeness',
                'gallery_urls', 'tier', 'featured_until', 'website_last_checked_at', 'website_link_broken',
                'enrichment_last_error', 'rating_avg_trust', 'rating_avg_games', 'rating_avg_support',
                'rating_avg_payments', 'rating_avg_bonuses',
            ]);
        });
    }
};
