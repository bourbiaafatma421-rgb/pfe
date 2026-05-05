<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_id')->constrained()->onDelete('cascade');
            $table->integer('month_number');
            $table->integer('week_number');
            $table->string('day_name')->nullable();       // lundi, mardi... (null pour mois 3+)
            $table->text('task_title');
            $table->text('objective')->nullable();
            $table->enum('type', [
                'administratif',
                'technique',
                'humain',
                'Réunion/Rencontre'
            ])->default('technique');
            $table->date('deadline')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', [
                'en_attente',
                'en_cours',
                'termine'
            ])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_tasks');
    }
};