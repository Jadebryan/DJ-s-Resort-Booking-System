<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_rbac_roles', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16); // staff | customer
            $table->string('slug', 64);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->foreignId('updated_by_tenant_user_id')->nullable()->constrained('tenant_users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kind', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_rbac_roles');
    }
};
