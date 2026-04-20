<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_registration_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('subscription_days')->nullable()->after('subscription_months');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_registration_requests', function (Blueprint $table) {
            $table->dropColumn('subscription_days');
        });
    }
};
