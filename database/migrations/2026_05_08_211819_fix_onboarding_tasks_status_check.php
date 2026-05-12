<?php
// php artisan make:migration fix_onboarding_tasks_status_check

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supprimer l'ancienne contrainte CHECK
        DB::statement('ALTER TABLE onboarding_tasks DROP CONSTRAINT IF EXISTS onboarding_tasks_status_check');

        // 2. Recréer avec tous les statuts du workflow
        DB::statement("
            ALTER TABLE onboarding_tasks
            ADD CONSTRAINT onboarding_tasks_status_check
            CHECK (status IN ('en_attente','en_cours','en_validation','rejetee','termine'))
        ");
    }

    public function down(): void
    {
        // Revenir à l'ancienne contrainte
        DB::statement('ALTER TABLE onboarding_tasks DROP CONSTRAINT IF EXISTS onboarding_tasks_status_check');

        DB::statement("
            ALTER TABLE onboarding_tasks
            ADD CONSTRAINT onboarding_tasks_status_check
            CHECK (status IN ('en_attente','en_cours','termine'))
        ");
    }
};