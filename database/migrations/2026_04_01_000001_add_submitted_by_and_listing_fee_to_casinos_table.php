<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casinos', function (Blueprint $table) {
            $table->foreignId('submitted_by_user_id')->nullable()->after('claimed_by_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('listing_fee_paid_at')->nullable()->after('submitted_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('casinos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by_user_id');
            $table->dropColumn('listing_fee_paid_at');
        });
    }
};
