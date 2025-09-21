<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aktivitas_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode')->nullOnDelete();
            $table->foreignId('list_aktivitas_id')->constrained('aktivitas_lists')->cascadeOnDelete();
            $table->date('tanggal_aktivitas')->nullable();
            $table->text('description')->nullable();
            $table->string('bukti_kegiatan')->nullable(); // BLOB
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kelompok_id','periode_id']);
            $table->index(['list_aktivitas_id','position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_cards');
    }
};
