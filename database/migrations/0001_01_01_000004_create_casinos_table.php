<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casinos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country');
            $table->string('country_slug');
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('logo_alt')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->string('screenshot_alt')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->unsignedSmallInteger('established_year')->nullable();
            $table->string('license')->nullable();
            $table->string('language')->nullable();
            $table->json('software_providers')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_claimed')->default(false);
            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->json('schema_data')->nullable();
            $table->string('enrichment_status')->default('pending');
            $table->timestamp('news_last_fetched_at')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'country_slug']);
            $table->index('enrichment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casinos');
    }
};
