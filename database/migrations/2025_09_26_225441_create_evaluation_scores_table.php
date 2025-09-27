<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationScoresTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('evaluation_scores')) {
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesi_id');         // id sesi evaluasi (FK ke tabel sesi Anda)
            $table->unsignedBigInteger('mahasiswa_id');    // id mahasiswa
            $table->string('indicator_code');              // FK -> indicators.code
            $table->unsignedTinyInteger('score')->default(0); // 0..100
            $table->unsignedBigInteger('evaluated_by')->nullable(); // user id
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sesi_id','mahasiswa_id','indicator_code']);
            $table->foreign('indicator_code')->references('code')->on('evaluation_rubric_indicators')->cascadeOnDelete();

            // Opsional: tambahkan FK ke tabel sesi/mahasiswa/user jika ada
            $table->foreign('sesi_id')->references('id')->on('evaluasi_sesi')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('evaluated_by')->references('id')->on('users')->nullOnDelete();
        });
        }
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_scores');
    }
}
