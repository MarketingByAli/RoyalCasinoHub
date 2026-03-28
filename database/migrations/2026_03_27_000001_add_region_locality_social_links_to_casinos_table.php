<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casinos', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('locality')->nullable();
            $table->json('social_links')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('casinos', function (Blueprint $table) {
            $table->dropColumn(['region', 'locality', 'social_links']);
        });
    }
};
