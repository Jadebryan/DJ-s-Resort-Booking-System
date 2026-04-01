<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_plan_upgrade_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('current_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('requested_plan_id')->constrained('plans')->cascadeOnDelete();
            $table->unsignedSmallInteger('requested_months')->default(1);
            $table->string('payment_method', 80);
            $table->string('payment_reference', 120)->nullable();
            $table->text('payment_notes')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_plan_upgrade_requests');
    }
};
