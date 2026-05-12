<?php
// php artisan make:migration rename_task_comments_to_match_model

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Renommer les colonnes pour correspondre au modèle
        Schema::table('task_comments', function (Blueprint $t) {
            $t->renameColumn('task_id',   'onboarding_task_id');
            $t->renameColumn('body',      'content');
            $t->renameColumn('file_path', 'attachment_path');
            $t->renameColumn('file_name', 'attachment_name');
            $t->renameColumn('file_mime', 'attachment_mime');
            // Ajouter la colonne 'link' qui manque
            $t->string('link')->nullable()->after('attachment_mime');
        });

        // 2. Renommer la table
        Schema::rename('task_comments', 'onboarding_task_comments');
    }

    public function down(): void
    {
        Schema::table('onboarding_task_comments', function (Blueprint $t) {
            $t->renameColumn('onboarding_task_id', 'task_id');
            $t->renameColumn('content',            'body');
            $t->renameColumn('attachment_path',    'file_path');
            $t->renameColumn('attachment_name',    'file_name');
            $t->renameColumn('attachment_mime',    'file_mime');
            $t->dropColumn('link');
        });

        Schema::rename('onboarding_task_comments', 'task_comments');
    }
};