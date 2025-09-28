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
        Schema::create('evaluasi_mitra', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Foreign keys
            $table->foreignId('evaluasi_sesi_id')->nullable()->constrained('evaluasi_master')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable(false)->constrained('periode')->cascadeOnDelete();
            $table->foreignId('kelompok_id')->nullable(false)->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('project_card_id')->constrained('project_cards')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Evaluation criteria (Mitra)
            $table->integer('m_kehadiran')->nullable()->comment('Penilaian kehadiran / komunikasi (0-100)');
            $table->integer('m_presentasi')->nullable()->comment('Penilaian hasil pekerjaan / presentasi (0-100)');

            // Calculated fields
            $table->decimal('rata_rata', 5, 2)->nullable()->comment('Rata-rata nilai mitra');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Nilai akhir dengan pembobotan');

            // Metadata
            $table->date('tanggal_evaluasi')->nullable()->comment('Tanggal evaluasi mitra');
            $table->string('status', 20)->default('draft')->comment('draft, submitted, locked');
            $table->text('catatan')->nullable()->comment('Catatan dari mitra');
            $table->decimal('progress_percentage', 5, 2)->default(0.00)->comment('Progress proyek saat evaluasi');
            $table->string('evaluation_type')->default('regular')->comment('regular, remedial, improvement');

            // Tracking
            $table->timestamp('submitted_at')->nullable()->comment('Tanggal penilaian disubmit');
            $table->timestamp('locked_at')->nullable()->comment('Tanggal penilaian dikunci');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete()->comment('User yang mengunci penilaian');

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint per mahasiswa & proyek
            $table->unique(['evaluasi_sesi_id', 'mahasiswa_id', 'project_card_id'], 'uniq_mitra_eval_per_student_project');

            // Indexes
            $table->index(['periode_id', 'kelompok_id', 'mahasiswa_id'], 'mitra_idx_periode_kelompok_mahasiswa');
            $table->index(['evaluasi_sesi_id', 'status'], 'mitra_idx_sesi_status');
            $table->index(['project_card_id', 'nilai_akhir'], 'mitra_idx_project_score');
            $table->index(['deleted_at'], 'mitra_idx_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_mitra');
    }
};
