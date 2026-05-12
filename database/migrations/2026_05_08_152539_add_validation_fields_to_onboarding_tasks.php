<?php
// database/migrations/xxxx_add_validation_fields_to_onboarding_tasks.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_tasks', function (Blueprint $t) {
            $t->string('status')->default('en_attente')->change();
            $t->text('rejection_reason')->nullable()->after('completion_date');
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_tasks', function (Blueprint $t) {
            $t->dropColumn('rejection_reason');
        });
    }
};