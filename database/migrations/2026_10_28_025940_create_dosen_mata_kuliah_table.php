<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mata_kuliah_id', 'dosen_id'], 'dosen_matkul_unique');
        });

        // Migrate existing dosen_id from mata_kuliah table if present
        if (Schema::hasColumn('mata_kuliah', 'dosen_id')) {
            $existing = DB::table('mata_kuliah')->whereNotNull('dosen_id')->get(['id', 'dosen_id']);
            foreach ($existing as $row) {
                DB::table('dosen_mata_kuliah')->insertOrIgnore([
                    'mata_kuliah_id' => $row->id,
                    'dosen_id' => $row->dosen_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_mata_kuliah');
    }
};
