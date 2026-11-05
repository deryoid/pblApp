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
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('angkatan', 10)->nullable()->after('nama_mahasiswa');
            $table->string('nik', 30)->nullable()->after('angkatan');
            $table->string('jk', 10)->nullable()->after('nik');
            $table->string('nama_ibu', 255)->nullable()->after('jk');
            $table->string('agama', 50)->nullable()->after('nama_ibu');
            $table->string('tempat_lahir', 255)->nullable()->after('agama');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('no_hp', 30)->nullable()->after('alamat');
            $table->string('email_kampus', 255)->nullable()->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn([
                'angkatan',
                'nik',
                'jk',
                'nama_ibu',
                'agama',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'no_hp',
                'email_kampus',
            ]);
        });
    }
};
