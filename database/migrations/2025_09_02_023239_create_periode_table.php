<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('periode', function (Blueprint $table) {
            $table->id();                          // PK integer
            $table->uuid('uuid')->unique();        // ID publik
            $table->string('periode')->unique();   // contoh: "2025/2026 Ganjil"
            $table->enum('status_periode', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('periode');
    }
};