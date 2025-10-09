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
        if (Schema::hasTable('evaluasi_setting')) {
            return;
        }

        Schema::create('evaluasi_setting', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'w_dosen', 'd_hasil', etc.
            $table->text('value')->nullable(); // Store setting value
            $table->string('tipe')->default('string'); // 'string', 'integer', 'boolean'
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_setting');
    }
};
