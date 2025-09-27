<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationRubricIndicatorsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('evaluation_rubric_indicators')) {
            return; // tabel sudah ada
        }
        Schema::create('evaluation_rubric_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('group_code'); // FK -> groups.code
            $table->string('code')->unique(); // d_hasil, d_teknis, m_komunikasi, ap_kehadiran, ...
            $table->string('name');
            $table->unsignedTinyInteger('weight')->default(0); // persentase di dalam group
            $table->timestamps();

            $table->foreign('group_code')->references('code')->on('evaluation_rubric_groups')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_rubric_indicators');
    }
}
