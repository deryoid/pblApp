<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('project_cards', 'nama_mitra')) {
                $t->string('nama_mitra')->nullable()->after('labels');
            }
            if (!Schema::hasColumn('project_cards', 'skema_pbl')) {
                $t->string('skema_pbl', 50)->nullable()->after('nama_mitra');
            }
            if (!Schema::hasColumn('project_cards', 'tanggal_mulai')) {
                $t->date('tanggal_mulai')->nullable()->after('skema_pbl');
            }
            // Tambah kolom tanggal_selesai tanpa menghilangkan due_date untuk kompatibilitas
            if (!Schema::hasColumn('project_cards', 'tanggal_selesai')) {
                $t->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('project_cards', 'biaya_barang')) {
                $t->decimal('biaya_barang', 15, 2)->default(0)->after('tanggal_selesai');
            }
            if (!Schema::hasColumn('project_cards', 'biaya_jasa')) {
                $t->decimal('biaya_jasa', 15, 2)->default(0)->after('biaya_barang');
            }
            if (!Schema::hasColumn('project_cards', 'kendala')) {
                $t->text('kendala')->nullable()->after('progress');
            }
            if (!Schema::hasColumn('project_cards', 'catatan')) {
                $t->text('catatan')->nullable()->after('kendala');
            }
            if (!Schema::hasColumn('project_cards', 'status_proyek')) {
                $t->string('status_proyek', 20)->default('Proses')->after('catatan');
            }
            if (!Schema::hasColumn('project_cards', 'link_drive_proyek')) {
                $t->string('link_drive_proyek')->nullable()->after('status_proyek');
            }
            if (!Schema::hasColumn('project_cards', 'members')) {
                $t->json('members')->nullable()->after('link_drive_proyek');
            }

            // Index tambahan (gunakan nama agar tidak bentrok)
            $t->index(['kelompok_id', 'periode_id'], 'pc_kelompok_periode_idx');
            $t->index('status_proyek', 'pc_status_idx');
            $t->index('tanggal_selesai', 'pc_tselesai_idx');
        });

        // Migrasi data: isi tanggal_selesai dari due_date jika ada
        if (Schema::hasColumn('project_cards', 'due_date') && Schema::hasColumn('project_cards', 'tanggal_selesai')) {
            try {
                DB::statement("UPDATE project_cards SET tanggal_selesai = DATE(due_date) WHERE tanggal_selesai IS NULL AND due_date IS NOT NULL");
            } catch (\Throwable $e) {
                // abaikan jika driver tidak mendukung DATE() (sqlite) — view masih punya fallback
            }
        }
    }

    public function down(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            // Drop indexes terlebih dahulu
            try { $t->dropIndex('pc_kelompok_periode_idx'); } catch (\Throwable $e) {}
            try { $t->dropIndex('pc_status_idx'); } catch (\Throwable $e) {}
            try { $t->dropIndex('pc_tselesai_idx'); } catch (\Throwable $e) {}

            // Hapus kolom yang ditambahkan
            $cols = [
                'members','link_drive_proyek','status_proyek','catatan','kendala',
                'biaya_jasa','biaya_barang','tanggal_selesai','tanggal_mulai','skema_pbl','nama_mitra'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('project_cards', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};

