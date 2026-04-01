<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->foreignId('tenant_rbac_role_id')->nullable()->after('role')->constrained('tenant_rbac_roles')->nullOnDelete();
        });

        Schema::table('regular_users', function (Blueprint $table) {
            $table->foreignId('tenant_rbac_role_id')->nullable()->after('password')->constrained('tenant_rbac_roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_rbac_role_id');
        });

        Schema::table('regular_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_rbac_role_id');
        });
    }
};
