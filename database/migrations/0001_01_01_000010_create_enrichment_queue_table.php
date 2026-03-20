<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrichment_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casino_id')->constrained()->cascadeOnDelete();
            $table->string('job_type');
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->text('result')->nullable();
            $table->timestamps();

            $table->index(['status', 'job_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrichment_queue');
    }
};
