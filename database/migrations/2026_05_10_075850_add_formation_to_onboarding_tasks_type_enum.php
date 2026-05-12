<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne contrainte CHECK
        DB::statement("
            ALTER TABLE onboarding_tasks 
            DROP CONSTRAINT IF EXISTS onboarding_tasks_type_check
        ");

        // Recréer avec formation + supprimer 'Réunion/Rencontre' qui semble être une erreur
        DB::statement("
            ALTER TABLE onboarding_tasks 
            ADD CONSTRAINT onboarding_tasks_type_check 
            CHECK (type = ANY (ARRAY[
                'technique'::character varying,
                'administratif'::character varying,
                'humain'::character varying,
                'formation'::character varying
            ]))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE onboarding_tasks 
            DROP CONSTRAINT IF EXISTS onboarding_tasks_type_check
        ");

        DB::statement("
            ALTER TABLE onboarding_tasks 
            ADD CONSTRAINT onboarding_tasks_type_check 
            CHECK (type = ANY (ARRAY[
                'technique'::character varying,
                'administratif'::character varying,
                'humain'::character varying,
                'Réunion/Rencontre'::character varying
            ]))
        ");
    }
};