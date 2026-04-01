<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_plan_upgrade_requests', function (Blueprint $table) {
            $table->unsignedInteger('proration_days_remaining')->nullable()->after('payment_proof_path');
            $table->decimal('proration_credit_amount', 12, 2)->nullable()->after('proration_days_remaining');
            $table->decimal('proration_new_term_total', 12, 2)->nullable()->after('proration_credit_amount');
            $table->decimal('proration_amount_due', 12, 2)->nullable()->after('proration_new_term_total');
            $table->unsignedInteger('proration_base_days')->nullable()->after('proration_amount_due');
            $table->unsignedInteger('proration_rollover_days')->nullable()->after('proration_base_days');
            $table->unsignedInteger('proration_total_days')->nullable()->after('proration_rollover_days');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_plan_upgrade_requests', function (Blueprint $table) {
            $table->dropColumn([
                'proration_days_remaining',
                'proration_credit_amount',
                'proration_new_term_total',
                'proration_amount_due',
                'proration_base_days',
                'proration_rollover_days',
                'proration_total_days',
            ]);
        });
    }
};
