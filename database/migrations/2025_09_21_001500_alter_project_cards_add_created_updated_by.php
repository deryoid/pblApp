<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('project_cards', 'created_by')) {
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('members');
            }
            if (!Schema::hasColumn('project_cards', 'updated_by')) {
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_cards', function (Blueprint $t) {
            if (Schema::hasColumn('project_cards', 'updated_by')) {
                $t->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('project_cards', 'created_by')) {
                $t->dropConstrainedForeignId('created_by');
            }
        });
    }
};

