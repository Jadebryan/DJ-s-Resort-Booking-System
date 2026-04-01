<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('tenant_name');
            $table->string('primary_domain');
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('admin_password');
            $table->string('status', 32)->default('awaiting_payment');
            $table->string('payment_provider', 32)->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('submitted_for_review_at')->nullable();
            $table->foreignId('approved_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_registration_requests');
    }
};
