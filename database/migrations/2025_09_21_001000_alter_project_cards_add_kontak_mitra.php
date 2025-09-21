<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('project_cards', 'kontak_mitra')) {
                $t->string('kontak_mitra')->nullable()->after('nama_mitra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (Schema::hasColumn('project_cards', 'kontak_mitra')) {
                $t->dropColumn('kontak_mitra');
            }
        });
    }
};

