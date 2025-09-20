<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode')->cascadeOnDelete();
            $table->foreignId('list_id')->nullable()->constrained('project_lists')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('labels')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('attachments_count')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
            $table->index(['list_id','position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_cards');
    }
};
