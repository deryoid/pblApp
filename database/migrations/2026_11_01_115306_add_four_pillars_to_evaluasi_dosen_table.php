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
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->integer('d_relevansi')->nullable()->after('evaluator_id')->comment('Relevansi Konsep (0-100)');
            $table->integer('d_kualitas')->nullable()->after('d_relevansi')->comment('Kualitas Produk (0-100)');
            $table->integer('d_kreativitas')->nullable()->after('d_kualitas')->comment('Kreativitas (0-100)');
            $table->integer('d_estetika')->nullable()->after('d_kreativitas')->comment('Estetika (0-100)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->dropColumn(['d_relevansi', 'd_kualitas', 'd_kreativitas', 'd_estetika']);
        });
    }
};
