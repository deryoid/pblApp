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
        Schema::create('evaluasi_dosen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Foreign keys
            $table->foreignId('evaluasi_master_id')->constrained('evaluasi_master')->onDelete('cascade');
            $table->foreignId('periode_id')->nullable(false)->constrained('periode')->onDelete('cascade');
            $table->foreignId('kelompok_id')->nullable(false)->constrained('kelompok')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('project_card_id')->constrained('project_cards')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();

            // Evaluation criteria
            $table->integer('d_hasil')->nullable()->comment('Nilai hasil (0-100)');
            $table->integer('d_teknis')->nullable()->comment('Nilai teknis (0-100)');
            $table->integer('d_user')->nullable()->comment('Nilai user experience (0-100)');
            $table->integer('d_efisiensi')->nullable()->comment('Nilai efisiensi (0-100)');
            $table->integer('d_dokpro')->nullable()->comment('Nilai dokumen proses (0-100)');
            $table->integer('d_inisiatif')->nullable()->comment('Nilai inisiatif (0-100)');

            // Calculated fields
            $table->decimal('rata_rata', 5, 2)->nullable()->comment('Rata-rata semua kriteria');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Nilai akhir dengan pembobotan');

            // Metadata
            $table->date('tanggal_evaluasi')->nullable()->comment('Tanggal evaluasi');
            $table->string('status', 20)->default('draft')->comment('draft, submitted, locked');
            $table->text('catatan')->nullable()->comment('Catatan evaluasi');
            $table->decimal('progress_percentage', 5, 2)->default(0.00)->comment('Persentase progres proyek saat evaluasi');
            $table->string('evaluation_type')->default('regular')->comment('regular, remedial, improvement');

            // Tracking
            $table->timestamp('submitted_at')->nullable()->comment('Tanggal penilaian disubmit');
            $table->timestamp('locked_at')->nullable()->comment('Tanggal penilaian dikunci');
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null')->comment('User yang mengunci penilaian');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint to prevent duplicates
            $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'project_card_id'], 'unique_evaluasi_per_mahasiswa_project');

            // Performance indexes
            $table->index(['periode_id', 'kelompok_id', 'mahasiswa_id'], 'idx_periode_kelompok_mahasiswa');
            $table->index(['evaluasi_master_id', 'status'], 'idx_sesi_status');
            $table->index(['evaluator_id', 'tanggal_evaluasi'], 'idx_evaluator_date');
            $table->index(['project_card_id', 'nilai_akhir'], 'idx_project_score');
            $table->index(['deleted_at'], 'idx_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_dosen');
    }
};
