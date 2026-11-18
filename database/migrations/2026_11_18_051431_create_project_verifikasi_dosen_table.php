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
        Schema::create('project_verifikasi_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_card_id')->constrained('project_cards')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->string('peran', 60)->default('Dosen Pengampu');
            $table->string('status', 20)->default('pending'); // pending, acc, revisi
            $table->text('catatan')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['project_card_id', 'dosen_id'], 'proj_card_dosen_verif_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_verifikasi_dosen');
    }
};
