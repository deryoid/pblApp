<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('evaluation_settings')) {
            return;
        }
        Schema::create('evaluation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value'); // simpan string, angka, json (bebas)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_settings');
    }
}
