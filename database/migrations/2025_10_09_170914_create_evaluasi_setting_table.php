<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: This migration is now a no-op because the application uses
     * 'evaluation_settings' table instead (created by 2025_09_26_225327).
     * This migration is kept for backward compatibility only.
     */
    public function up(): void
    {
        // No-op - evaluation_settings table is used instead
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
