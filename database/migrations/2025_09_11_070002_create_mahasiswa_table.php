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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();                              // PK integer
            $table->uuid('uuid')->unique();            // UUID untuk URL

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique('user_id');                 // 1 user : 1 mahasiswa

            $table->string('nim')->unique();
            $table->string('nama_mahasiswa');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
