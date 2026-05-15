<?php

namespace App\Services;

use App\Models\Onboarding;
use App\Models\OnboardingTask;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnboardingAIService
{
    // ── URL de l'API Colab (ngrok) ────────────────────────
    private function getAiUrl(): string
    {
        // 1. Depuis le cache (mis à jour automatiquement par Colab)
        $url = Cache::get('ai_service_url');

        // 2. Fallback depuis .env
        if (!$url) {
            $url = env('AI_SERVICE_URL');
        }

        if (!$url) {
            throw new \Exception('AI_SERVICE_URL non configuré. Lance Colab d\'abord.');
        }

        return rtrim($url, '/');
    }

    // ── Générer le plan via GPT ───────────────────────────
    public function genererPlan(User $user, string $poste, string $description, int $monthsCount): Onboarding
    {
        // 1. Vérifier que le CV existe
        if (!$user->cv_data) {
            throw new \Exception('CV non trouvé pour cet utilisateur.');
        }

        // 2. Appel API Colab
        $aiUrl = $this->getAiUrl();

        Log::info('OnboardingAI: appel API', ['url' => $aiUrl, 'user' => $user->id]);

        $response = Http::timeout(180)  // 3 min max (GPT peut être lent)
            ->post("{$aiUrl}/generate-plan", [
                'cv'          => $user->cv_data,
                'poste'       => $poste,
                'description' => $description,
                'months_count'=> $monthsCount,
            ]);

        if (!$response->successful()) {
            Log::error('OnboardingAI: erreur API', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('Erreur API IA : ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            throw new \Exception('L\'IA a retourné une erreur : ' . json_encode($data));
        }

        $plan = $data['plan'];
        Log::info('OnboardingAI: plan brut', ['plan' => json_encode($plan)]);
        // 3. Créer l'onboarding
        $onboarding = Onboarding::updateOrCreate(
            ['user_id' => $user->id],
            [
                'generated_by' => 'IA',
                'status'       => 'genere',
            ]
        );

        // 4. Supprimer les anciennes tâches si régénération
        $onboarding->tasks()->delete();

        // 5. Parser le plan JSON et insérer les tâches
        $this->parsePlanToTasks($onboarding, $plan, $user->date_of_hire);

        Log::info('OnboardingAI: plan généré', ['onboarding_id' => $onboarding->id]);

        return $onboarding->load('tasks');
    }

    // ── Parser le JSON GPT → onboarding_tasks ────────────
private function parsePlanToTasks(Onboarding $onboarding, array $plan, ?string $dateEmbauche): void
    {
        $tasks    = [];
        $planData = $plan['plan'] ?? $plan;
        $startDate = $dateEmbauche 
            ? \Carbon\Carbon::parse($dateEmbauche) 
            : \Carbon\Carbon::now();
        foreach ($planData as $moisKey => $moisData) {
            // mois_1, mois_2, etc.
            $monthNumber = (int) filter_var($moisKey, FILTER_SANITIZE_NUMBER_INT);

            if (!is_array($moisData)) continue;

            $semaines = $moisData['semaines'] ?? $moisData;

            foreach ($semaines as $semaineKey => $semaineData) {
                $weekNumber = (int) filter_var($semaineKey, FILTER_SANITIZE_NUMBER_INT);

                if (!is_array($semaineData)) continue;

                // Mois 1 et 2 → détail par jour
                if (isset($semaineData['lundi']) || isset($semaineData['mardi'])) {
                    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];

                    foreach ($jours as $jour) {
                        if (!isset($semaineData[$jour])) continue;

                        $tache = $semaineData[$jour];

                        $tasks[] = [
                            'onboarding_id' => $onboarding->id,
                            'month_number'  => $monthNumber,
                            'week_number'   => $weekNumber,
                            'day_name'      => $jour,
                            'task_title'    => $tache['tâche']      
                                ?? $tache['tache']               
                                ?? $tache['titre'] 
                                ?? $tache['activite']
                                ?? $tache['task_title']
                                ?? $tache['description']
                                ?? 'Tâche',        
                            'objective'     => $tache['objectif'] ?? null,
                            'type'          => $this->normalizeType($tache['type'] ?? 'technique'),
                            'deadline'      => $this->calcDeadline($startDate, $monthNumber, $weekNumber, $jour),
                            'completion_date' => null,
                            'status'        => 'en_attente',
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ];
                    }
                } else {
                    // Mois 3+ → format hebdomadaire (pas de jours)
                    $tasks[] = [
                        'onboarding_id' => $onboarding->id,
                        'month_number'  => $monthNumber,
                        'week_number'   => $weekNumber,
                        'day_name'      => null,
                        'task_title'    => $semaineData['objectif_principal']
                            ?? $semaineData['objectif'] 
                            ?? $semaineData['titre'] 
                            ?? 'Objectif semaine',
                        'objective'     => $semaineData['livrable'] 
                            ?? $semaineData['livrable_attendu']
                            ?? null,
                        'type'          => 'technique',
                        'deadline'      => $this->calcDeadline($startDate, $monthNumber, $weekNumber, 'vendredi'),
                        'completion_date' => null,
                        'status'        => 'en_attente',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }
        }

        // Insert en batch
        if (!empty($tasks)) {
            OnboardingTask::insert($tasks);
        }
    }

    // ── Calcul deadline ───────────────────────────────────
    private function calcDeadline(\Carbon\Carbon $startDate, int $month, int $week, string $day): string
    {
        $jours = ['lundi' => 0, 'mardi' => 1, 'mercredi' => 2, 'jeudi' => 3, 'vendredi' => 4];
        $dayOffset = $jours[$day] ?? 4;

        return $startDate->copy()
            ->addMonths($month - 1)
            ->addWeeks($week - 1)
            ->addDays($dayOffset)
            ->toDateString();
    }

    // ── Normaliser le type ────────────────────────────────
    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        $map = [
            'administratif'      => 'administratif',
            'admin'              => 'administratif',
            'technique'          => 'technique',
            'tech'               => 'technique',
            'humain'             => 'humain',
            'human'              => 'humain',
            'réunion/rencontre'  => 'humain',
            'reunion'            => 'humain',
            'rencontre'          => 'humain',
            'formation'          => 'formation',
            'formation/ressource'=> 'formation',
            'ressource'          => 'formation',
        ];

        return $map[$type] ?? 'technique';
    }

    // ── Health check Colab ────────────────────────────────
    public function isColabOnline(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->getAiUrl() . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
      public function submitAvis(array $payload): array
    {
        $aiUrl = env('AI_SERVICE_URL');
 
        if (!$aiUrl) {
            Log::warning('AI_SERVICE_URL non configuré — avis non envoyé.');
            return ['success' => false, 'message' => 'URL IA non configurée'];
        }
 
        try {
            $response = Http::timeout(30)
                            ->post("{$aiUrl}/submit-avis", $payload);
 
            if ($response->successful()) {
                return $response->json();
            }
 
            Log::error('Colab submit-avis failed: ' . $response->status());
            return ['success' => false, 'message' => 'Erreur Colab ' . $response->status()];
 
        } catch (\Exception $e) {
            Log::error('Colab submit-avis exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}