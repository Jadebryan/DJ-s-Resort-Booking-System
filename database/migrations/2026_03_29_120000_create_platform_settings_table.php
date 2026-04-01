<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('timezone', 64)->default('Asia/Manila');
            $table->boolean('send_system_emails')->default(true);
            $table->boolean('send_sms_alerts')->default(false);
            $table->boolean('feature_booking_calendar_beta')->default(false);
            $table->boolean('feature_multi_currency')->default(false);
            $table->timestamps();
        });

        DB::table('platform_settings')->insert([
            'default_plan_id' => null,
            'timezone' => config('app.timezone', 'Asia/Manila'),
            'send_system_emails' => true,
            'send_sms_alerts' => false,
            'feature_booking_calendar_beta' => false,
            'feature_multi_currency' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
