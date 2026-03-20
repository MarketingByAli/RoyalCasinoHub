<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casino_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casino_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url', 2048);
            $table->string('source')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['casino_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casino_news');
    }
};
