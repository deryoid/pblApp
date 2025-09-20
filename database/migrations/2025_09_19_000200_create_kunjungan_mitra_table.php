<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_mitra', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('perusahaan');
            $table->string('alamat', 255);
            $table->date('tanggal_kunjungan');
            $table->enum('status_kunjungan', ['Sudah dikunjungi','Proses Pembicaraan','Tidak ada tanggapan','Ditolak'])->default('Sudah dikunjungi');

            $table->string('bukti_kunjungan_mime', 100)->nullable();
            $table->timestamps();
        });

        // Add LONGBLOB using raw statement for broad MySQL versions
        DB::statement("ALTER TABLE `kunjungan_mitra` ADD `bukti_kunjungan` LONGBLOB NULL AFTER `bukti_kunjungan_mime`");
    }

    public function down(): void
    {
        if (Schema::hasTable('kunjungan_mitra')) {
            Schema::drop('kunjungan_mitra');
        }
    }
};
