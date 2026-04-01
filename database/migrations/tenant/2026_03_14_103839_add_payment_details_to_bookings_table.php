<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payer_full_name')->nullable()->after('payment_proof_path');
            $table->string('payer_gcash_no')->nullable()->after('payer_full_name');
            $table->string('payer_ref_no')->nullable()->after('payer_gcash_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payer_full_name', 'payer_gcash_no', 'payer_ref_no']);
        });
    }
};
