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
        Schema::table('project_cards', function (Blueprint $table) {
            $table->enum('jenis_proyek', ['internal', 'external'])->default('internal')->after('description');
            $table->string('topik_proyek')->nullable()->after('jenis_proyek');
            $table->enum('sumber_proyek', ['dosen', 'lomba', 'mandiri', 'lainnya'])->nullable()->after('topik_proyek');
            $table->foreignId('dosen_penentu_id')->nullable()->after('sumber_proyek')->constrained('users')->nullOnDelete();
            $table->text('alamat_mitra')->nullable()->after('kontak_mitra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_cards', function (Blueprint $table) {
            $table->dropForeign(['dosen_penentu_id']);
            $table->dropColumn([
                'jenis_proyek',
                'topik_proyek',
                'sumber_proyek',
                'dosen_penentu_id',
                'alamat_mitra',
            ]);
        });
    }
};
