<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('betting_deposit_methods', function (Blueprint $table) {
            $table->id();
            $table->string('coin_name', 64);
            $table->string('network', 64)->nullable();
            $table->string('address', 255);
            $table->text('instructions')->nullable();
            $table->string('qr_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('betting_withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deposit_method_id')->nullable()->constrained('betting_deposit_methods')->nullOnDelete();
            $table->string('coin_name', 64);
            $table->string('network', 64)->nullable();
            $table->string('destination_address', 255);
            $table->decimal('amount', 16, 2);
            $table->string('status', 32)->default('pending');
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('betting_deposit_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deposit_method_id')->nullable()->constrained('betting_deposit_methods')->nullOnDelete();
            $table->decimal('amount', 16, 2)->nullable();
            $table->string('tx_hash', 255)->nullable();
            $table->text('user_note')->nullable();
            $table->string('status', 32)->default('pending');
            $table->decimal('credited_amount', 16, 2)->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betting_deposit_notices');
        Schema::dropIfExists('betting_withdraw_requests');
        Schema::dropIfExists('betting_deposit_methods');
    }
};
