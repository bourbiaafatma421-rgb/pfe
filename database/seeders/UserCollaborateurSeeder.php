<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Collaborateur;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserCollaborateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $password = Str::random(8);

        $user = User::create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'role' => 'collaborateur',
        ]);

        $collab = Collaborateur::create([
            'user_id' => $user->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'numero_telephone' => '+21612345678',
            'poste' => 'Dev',
        ]);

        echo "Utilisateur créé avec email: {$user->email} et mot de passe: $password\n";
    }
 }
