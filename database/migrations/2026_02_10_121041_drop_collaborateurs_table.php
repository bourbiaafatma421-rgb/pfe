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
<<<<<<< HEAD
=======
<<<<<<<< HEAD:database/migrations/2026_02_11_133012_create_role_table.php
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->timestamps();
        });
<<<<<<< HEAD
=======
========
>>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510:database/migrations/2026_02_10_121041_drop_collaborateurs_table.php
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
        Schema::dropIfExists('collaborateurs');
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('role');
=======
<<<<<<<< HEAD:database/migrations/2026_02_11_133012_create_role_table.php
        Schema::dropIfExists('role');
========
>>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510:database/migrations/2026_02_10_121041_drop_collaborateurs_table.php
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
        Schema::create('collaborateurs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
<<<<<<< HEAD
     }
=======
    }
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
};