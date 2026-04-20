<?php

namespace App\Services;

use App\Models\User;

class IntegrationPlanService
{
    public function getByUser(User $user): array
    {
        if ($user->id === 9) {
            return [
                "user_id" => $user->id,
                "phases" => [
                    [
                        "phase" => "Semaine 1",
                        "title" => "Accueil et découverte de l'entreprise",
                        "status" => "completed",
                        "progress" => 100,
                        "tasks" => [
                            ["id" => 1, "title" => "Réception du matériel informatique", "completed" => true],
                            ["id" => 2, "title" => "Configuration des accès et comptes", "completed" => true],
                            ["id" => 3, "title" => "Visite des locaux et présentation des équipes", "completed" => true],
                            ["id" => 4, "title" => "Rencontre avec le responsable hiérarchique", "completed" => true],
                            ["id" => 5, "title" => "Présentation de l'entreprise et ses valeurs", "completed" => true],
                        ],
                    ],
                    [
                        "phase" => "Semaine 2",
                        "title" => "Formation aux outils et processus",
                        "status" => "in-progress",
                        "progress" => 60,
                        "tasks" => [
                            ["id" => 6, "title" => "Formation sur les outils de communication", "completed" => true],
                            ["id" => 7, "title" => "Formation sur le système CRM", "completed" => true],
                            ["id" => 8, "title" => "Formation sur les processus internes", "completed" => true],
                            ["id" => 9, "title" => "Session avec le tuteur/mentor", "completed" => false],
                            ["id" => 10, "title" => "Quiz de validation des connaissances", "completed" => false],
                        ],
                    ],
                    [
                        "phase" => "Semaine 3",
                        "title" => "Intégration opérationnelle",
                        "status" => "upcoming",
                        "progress" => 0,
                        "tasks" => [
                            ["id" => 11, "title" => "Participation aux réunions d'équipe", "completed" => false],
                            ["id" => 12, "title" => "Prise en main du premier projet", "completed" => false],
                            ["id" => 13, "title" => "Observation et accompagnement terrain", "completed" => false],
                            ["id" => 14, "title" => "Point d'étape avec le manager", "completed" => false],
                        ],
                    ],
                    [
                        "phase" => "Semaine 4",
                        "title" => "Montée en autonomie",
                        "status" => "upcoming",
                        "progress" => 0,
                        "tasks" => [
                            ["id" => 15, "title" => "Gestion autonome des missions", "completed" => false],
                            ["id" => 16, "title" => "Présentation de ses premières réalisations", "completed" => false],
                            ["id" => 17, "title" => "Entretien de fin de période d'essai", "completed" => false],
                            ["id" => 18, "title" => "Élaboration du plan de développement", "completed" => false],
                        ],
                    ],
                ],
            ];
        }

        // Autres utilisateurs (par défaut)
        return [
            "user_id" => $user->id,
            "phases" => [
                [
                    "phase" => "Semaine 1",
                    "title" => "Découverte générale",
                    "status" => "not-started",
                    "progress" => 0,
                    "tasks" => [
                        ["id" => 1, "title" => "Première tâche générique", "completed" => false],
                    ],
                ],
            ],
        ];
    }
}