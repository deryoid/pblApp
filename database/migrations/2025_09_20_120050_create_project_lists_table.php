<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->string('color')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamps();
            $table->unique(['kelompok_id','periode_id','position']);
            $table->index(['kelompok_id','periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_lists');
    }
};

