<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode')->cascadeOnDelete();
            $table->foreignId('list_id')->nullable()->constrained('project_lists')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('labels')->nullable();
            // tanggal
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->dateTime('due_date')->nullable(); // kompat

            // atribut mitra/skema
            $table->string('nama_mitra')->nullable();
            $table->string('kontak_mitra')->nullable();
            $table->string('skema_pbl', 50)->nullable();

            // biaya
            $table->decimal('biaya_barang', 15, 2)->default(0);
            $table->decimal('biaya_jasa', 15, 2)->default(0);

            // metadata
            $table->text('kendala')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status_proyek', 20)->default('Proses');
            $table->string('status')->default('Proses'); // kompat name
            $table->string('link_drive_proyek')->nullable();

            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('attachments_count')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);

            // audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['list_id','position']);
            $table->index(['kelompok_id','periode_id'],'pc_kelompok_periode_idx');
            $table->index('status_proyek','pc_status_idx');
            $table->index('tanggal_selesai','pc_tselesai_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_cards');
    }
};
