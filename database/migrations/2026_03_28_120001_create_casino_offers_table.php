<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casino_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casino_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('welcome_bonus_text')->nullable();
            $table->string('wagering_requirement')->nullable();
            $table->unsignedSmallInteger('free_spins')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('source')->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casino_offers');
    }
};
