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
        Schema::create('project_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_card_id')->constrained('project_cards')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_card_id', 'mata_kuliah_id'], 'proj_matkul_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_mata_kuliah');
    }
};
