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
        // 1. Tambah skema_kategori pada tabel kelompok
        if (Schema::hasTable('kelompok') && ! Schema::hasColumn('kelompok', 'skema_kategori')) {
            Schema::table('kelompok', function (Blueprint $table) {
                $table->string('skema_kategori', 50)->default('Internal')->after('nama_kelompok');
            });
        }

        // 2. Tambah berkas proposal pada project_cards
        if (Schema::hasTable('project_cards')) {
            Schema::table('project_cards', function (Blueprint $table) {
                if (! Schema::hasColumn('project_cards', 'proposal_file_path')) {
                    $table->string('proposal_file_path', 255)->nullable()->after('link_drive_proyek');
                }
                if (! Schema::hasColumn('project_cards', 'status_proposal')) {
                    $table->string('status_proposal', 30)->default('pending')->after('proposal_file_path');
                }
            });
        }

        // 3. Tambah kolom type dan invited_by pada kelompok_join_requests
        if (Schema::hasTable('kelompok_join_requests')) {
            Schema::table('kelompok_join_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('kelompok_join_requests', 'type')) {
                    $table->enum('type', ['invite', 'request'])->default('invite')->after('status');
                }
                if (! Schema::hasColumn('kelompok_join_requests', 'invited_by')) {
                    $table->foreignId('invited_by')->nullable()->after('type')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kelompok_join_requests')) {
            Schema::table('kelompok_join_requests', function (Blueprint $table) {
                if (Schema::hasColumn('kelompok_join_requests', 'invited_by')) {
                    $table->dropForeign(['invited_by']);
                    $table->dropColumn('invited_by');
                }
                if (Schema::hasColumn('kelompok_join_requests', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }

        if (Schema::hasTable('project_cards')) {
            Schema::table('project_cards', function (Blueprint $table) {
                if (Schema::hasColumn('project_cards', 'status_proposal')) {
                    $table->dropColumn('status_proposal');
                }
                if (Schema::hasColumn('project_cards', 'proposal_file_path')) {
                    $table->dropColumn('proposal_file_path');
                }
            });
        }

        if (Schema::hasTable('kelompok') && Schema::hasColumn('kelompok', 'skema_kategori')) {
            Schema::table('kelompok', function (Blueprint $table) {
                $table->dropColumn('skema_kategori');
            });
        }
    }
};
