<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection('tenant')->table('bookings', function (Blueprint $table) {
            $table->string('paymongo_payment_intent_id', 80)->nullable()->after('payer_ref_no');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('bookings', function (Blueprint $table) {
            $table->dropColumn('paymongo_payment_intent_id');
        });
    }
};
