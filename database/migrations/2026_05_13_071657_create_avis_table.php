<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('collaborateur_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->integer('etoiles');
            $table->text('commentaire')->nullable();
            $table->string('rythme');        // bon | moyen | lent
            $table->integer('score_sante');
            $table->integer('duree_jours');
            $table->string('valide_par');
            $table->boolean('eligible')->default(false);
            $table->boolean('envoye_ia')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};