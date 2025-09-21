<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (Schema::hasColumn('project_cards', 'members')) {
                $t->dropColumn('members');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('project_cards', 'members')) {
                $t->json('members')->nullable()->after('link_drive_proyek');
            }
        });
    }
};

