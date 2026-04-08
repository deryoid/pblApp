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
        Schema::create('evaluation_rubric_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('group_code');
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('weight')->default(0);
            $table->timestamps();

            $table->foreign('group_code')->references('code')->on('evaluation_rubric_groups')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_rubric_indicators');
    }
};
