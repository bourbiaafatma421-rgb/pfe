<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
           $table->renameColumn('numero_telephone', 'phone_number');
           $table->renameColumn('date_recrutement', 'date_of_hire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('phone_number', 'numero_telephone');
            $table->renameColumn('date_of_hire', 'date_recrutement');
        });
    }
};
