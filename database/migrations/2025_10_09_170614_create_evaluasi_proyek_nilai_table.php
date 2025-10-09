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
        if (Schema::hasTable('evaluasi_proyek_nilai')) {
            return;
        }

        Schema::create('evaluasi_proyek_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->nullable()->constrained('evaluasi_master')->nullOnDelete();
            $table->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
            $table->string('jenis'); // 'dosen' or 'mitra'
            $table->json('nilai')->nullable(); // Store detailed scores
            $table->integer('total')->default(0); // Total score
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['sesi_id', 'card_id', 'jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_proyek_nilai');
    }
};
