<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Chemin vers la signature dessinée
            $table->string('signature_path')->nullable()->after('phone_number');
            // Token unique pour le QR Code
            $table->string('signature_token')->nullable()->unique()->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'signature_token']);
        });
    }
};