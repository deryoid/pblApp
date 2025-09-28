<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['evaluator_id']);

            // Change column to nullable
            $table->foreignId('evaluator_id')->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['evaluator_id']);

            // Change column back to not nullable
            $table->foreignId('evaluator_id')->nullable(false)->change();

            // Re-add foreign key constraint
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
