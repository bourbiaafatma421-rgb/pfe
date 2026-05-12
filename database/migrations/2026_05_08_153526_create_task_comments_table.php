<?php
// database/migrations/xxxx_create_task_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')
              ->constrained('onboarding_tasks')
              ->cascadeOnDelete();
            $t->foreignId('user_id')
              ->constrained('users')
              ->cascadeOnDelete();
            $t->text('body')->nullable();        // texte libre, lien GitHub, etc.
            $t->string('file_path')->nullable(); // chemin storage si fichier joint
            $t->string('file_name')->nullable(); // nom d'affichage
            $t->string('file_mime')->nullable(); // ex. application/pdf
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};