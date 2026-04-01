<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('regular_user_id')->nullable()->after('tenant_user_id')->constrained('regular_users')->nullOnDelete();
            $table->string('entity_type', 64)->nullable()->after('description');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            $table->json('payload')->nullable()->after('entity_id');
            $table->string('previous_hash', 64)->nullable()->after('payload');
            $table->string('row_hash', 64)->nullable()->after('previous_hash');
            $table->string('ip_address', 45)->nullable()->after('row_hash');
            $table->string('actor_type', 32)->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['regular_user_id']);
            $table->dropColumn([
                'regular_user_id',
                'entity_type',
                'entity_id',
                'payload',
                'previous_hash',
                'row_hash',
                'ip_address',
                'actor_type',
            ]);
        });
    }
};
