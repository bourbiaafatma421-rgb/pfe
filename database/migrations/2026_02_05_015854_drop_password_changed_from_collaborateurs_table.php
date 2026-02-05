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
        Schema::table('collaborateurs', function (Blueprint $table) {
            $table->dropColumn('password_changed'); 
        });
    }

    public function down(): void
    {
        Schema::table('collaborateurs', function (Blueprint $table) {
            $table->boolean('password_changed')->default(false);
        });
    }
};
