<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationRubricGroupsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('evaluation_rubric_groups')) {
            return; // tabel sudah ada, hindari error duplicate
        }
        Schema::create('evaluation_rubric_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ap, project_dosen, project_mitra
            $table->string('name');
            $table->unsignedTinyInteger('weight')->default(100); // bobot di parent (0..100)
            $table->string('parent_code')->nullable(); // null = root
            $table->timestamps();

            // Index optional untuk query tree; hindari FK self-referential agar tidak error di MySQL
            $table->index('parent_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_rubric_groups');
    }
}
