<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_cards', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();

            // Relasi
            $t->foreignId('kelompok_id')->nullable()->constrained('kelompok')->nullOnDelete();
            $t->foreignId('periode_id')->nullable()->constrained('periode')->nullOnDelete();
            $t->foreignId('list_aktivitas_id')->constrained('aktivitas_lists')->cascadeOnDelete();

            // Kolom utama (sesuai screenshot)
            $t->date('tanggal_aktivitas')->nullable();
            $t->text('description')->nullable();
            $t->string('bukti_kegiatan', 255)->nullable();
            $t->unsignedInteger('position')->default(0);

            // Audit
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();

            // Index bantu
            $t->index(['list_aktivitas_id','position','id'], 'akc_list_pos_id_idx');
            $t->index(['kelompok_id','periode_id'], 'akc_kelompok_periode_idx');
            $t->index('tanggal_aktivitas', 'akc_tanggal_idx');
        });
    }

    public function down(): void
    {
        // drop index & FK dulu agar aman di beberapa driver
        Schema::table('aktivitas_cards', function (Blueprint $t) {
            try { $t->dropIndex('akc_list_pos_id_idx'); } catch (\Throwable $e) {}
            try { $t->dropIndex('akc_kelompok_periode_idx'); } catch (\Throwable $e) {}
            try { $t->dropIndex('akc_tanggal_idx'); } catch (\Throwable $e) {}

            foreach (['updated_by','created_by','list_aktivitas_id','periode_id','kelompok_id'] as $fk) {
                if (Schema::hasColumn('aktivitas_cards', $fk)) {
                    try { $t->dropConstrainedForeignId($fk); } catch (\Throwable $e) {}
                }
            }
        });

        Schema::dropIfExists('aktivitas_cards');
    }
};
