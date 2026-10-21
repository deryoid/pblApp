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
        Schema::table('kelompok', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->nullable()->after('nama_kelompok')->index();
            $table->unsignedTinyInteger('max_members')->default(4)->after('semester');
            $table->enum('status_bergabung', ['terbuka', 'penuh', 'terkunci'])->default('terbuka')->after('max_members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelompok', function (Blueprint $table) {
            $table->dropColumn(['semester', 'max_members', 'status_bergabung']);
        });
    }
};
