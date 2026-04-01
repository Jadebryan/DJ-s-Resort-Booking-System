<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_registration_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('subscription_months')->default(1)->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_registration_requests', function (Blueprint $table) {
            $table->dropColumn('subscription_months');
        });
    }
};
