<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('platform_updates');
    }

    public function down(): void
    {
        // Intentionally empty: platform announcements feature was removed.
    }
};
