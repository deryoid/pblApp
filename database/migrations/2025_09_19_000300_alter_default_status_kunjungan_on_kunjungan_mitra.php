<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah default enum ke 'Sudah dikunjungi'
        DB::statement("ALTER TABLE `kunjungan_mitra` MODIFY `status_kunjungan` ENUM('Sudah dikunjungi','Proses Pembicaraan','Tidak ada tanggapan','Ditolak') NOT NULL DEFAULT 'Sudah dikunjungi'");
    }

    public function down(): void
    {
        // Kembalikan default sebelumnya ke 'Proses Pembicaraan'
        DB::statement("ALTER TABLE `kunjungan_mitra` MODIFY `status_kunjungan` ENUM('Sudah dikunjungi','Proses Pembicaraan','Tidak ada tanggapan','Ditolak') NOT NULL DEFAULT 'Proses Pembicaraan'");
    }
};

