<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class sttafSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      // Définir les emails et infos fixes
        $defaultStaffs = [
            [
                'role' => 'manager',
                'nom' => 'Hamdoun',
                'prenom' => 'Souad',
                'email' => 'HamdounSouad@gmail.com',
            ],
            [
                'role' => 'RH',
                'nom' => 'Makhlouf',
                'prenom' => 'Wafa',
                'email' => 'Makhloufwafa@gmail.com',
            ],
        ];

        foreach ($defaultStaffs as $staffData) {

            // Vérifie si l'email existe déjà
            if (!User::where('email', $staffData['email'])->exists()) {

                // Générer un mot de passe aléatoire de 10 caractères
                $randomPassword = Str::random(10);

                // Création du User
                $user = User::create([
                    'email' => $staffData['email'],
                    'password' => Hash::make($randomPassword),
                    'role' => $staffData['role'],
                ]);

                // Création du Staff
                Staff::create([
                    'user_id' => $user->id,
                    'role' => $staffData['role'],
                    'nom' => $staffData['nom'],
                    'prenom' => $staffData['prenom'],
                ]);

                // Affiche le mot de passe généré pour le noter
                echo "Créé : {$staffData['role']} - Email: {$staffData['email']} | Password: $randomPassword\n";
            }
        }
    }
}

