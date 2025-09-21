<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aktivitas_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode')->nullOnDelete();
            $table->string('name');
            $table->string('rentang_tanggal')->nullable();
            $table->string('link_drive_logbook')->nullable();
            $table->enum('status_evaluasi', ['Belum Evaluasi','Sudah Evaluasi'])->default('Belum Evaluasi');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['kelompok_id','periode_id']);
            $table->unique(['kelompok_id','periode_id','position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_lists');
    }
};
