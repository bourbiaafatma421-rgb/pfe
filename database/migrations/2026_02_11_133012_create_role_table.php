<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
<<<<<<<< HEAD:database/migrations/2026_02_11_133012_create_role_table.php
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->timestamps();
        });
========
>>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510:database/migrations/2026_02_10_121041_drop_collaborateurs_table.php
        Schema::dropIfExists('collaborateurs');
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations/2026_02_11_133012_create_role_table.php
        Schema::dropIfExists('role');
========
>>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510:database/migrations/2026_02_10_121041_drop_collaborateurs_table.php
        Schema::create('collaborateurs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};