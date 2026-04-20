<?php

namespace App\Services;

use App\Models\User;

class FormationService
{
    public function getByUser(User $user): array
    {
        if ($user->id === 9) {
            return [
                [
                    "id" => 1,
                    "title" => "Introduction aux outils collaboratifs",
                    "category" => "Obligatoire",
                    "duration" => "2h 30min",
                    "progress" => 100,
                    "modules" => 8,
                    "completedModules" => 8,
                    "instructor" => "Marie Dupont",
                    "level" => "Débutant",
                    "status" => "completed",
                ],
                [
                    "id" => 2,
                    "title" => "Sécurité informatique et RGPD",
                    "category" => "Obligatoire",
                    "duration" => "3h 15min",
                    "progress" => 65,
                    "modules" => 10,
                    "completedModules" => 7,
                    "instructor" => "Jean Martin",
                    "level" => "Débutant",
                    "status" => "in-progress",
                ],
                [
                    "id" => 3,
                    "title" => "Communication professionnelle",
                    "category" => "Recommandée",
                    "duration" => "1h 45min",
                    "progress" => 0,
                    "modules" => 6,
                    "completedModules" => 0,
                    "instructor" => "Sophie Bernard",
                    "level" => "Intermédiaire",
                    "status" => "not-started",
                ],
                [
                    "id" => 4,
                    "title" => "Gestion de projet Agile",
                    "category" => "Recommandée",
                    "duration" => "4h 00min",
                    "progress" => 0,
                    "modules" => 12,
                    "completedModules" => 0,
                    "instructor" => "Pierre Dubois",
                    "level" => "Intermédiaire",
                    "status" => "not-started",
                ],
                [
                    "id" => 5,
                    "title" => "Leadership et management",
                    "category" => "Optionnelle",
                    "duration" => "5h 30min",
                    "progress" => 0,
                    "modules" => 15,
                    "completedModules" => 0,
                    "instructor" => "Claire Rousseau",
                    "level" => "Avancé",
                    "status" => "not-started",
                ],
                [
                    "id" => 6,
                    "title" => "Maîtrise d'Excel avancé",
                    "category" => "Optionnelle",
                    "duration" => "3h 45min",
                    "progress" => 0,
                    "modules" => 9,
                    "completedModules" => 0,
                    "instructor" => "Thomas Petit",
                    "level" => "Avancé",
                    "status" => "not-started",
                ],
            ];
        }

        return [
            [
                "id" => 1,
                "title" => "Sécurité informatique",
                "category" => "Obligatoire",
                "duration" => "3h 15min",
                "progress" => 80,
                "modules" => 10,
                "completedModules" => 8,
                "instructor" => "Marie Dupont",
                "level" => "Débutant",
                "status" => "in-progress",
            ],
        ];
    }
}