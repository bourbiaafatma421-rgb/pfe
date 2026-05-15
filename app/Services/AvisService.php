<?php

namespace App\Services;

use App\Models\Avis;
use App\Models\Onboarding;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AvisService
{
    public function __construct(
        private OnboardingAIService $aiService
    ) {}

    public function calculerRythme(Onboarding $onboarding): string
    {
        $duree      = $onboarding->created_at->diffInDays(now());
        // ✅ progression() est une méthode — appel avec ()
        $progression = $onboarding->progression();

        if ($progression >= 80 && $duree <= 30) return 'bon';
        if ($progression >= 50)                 return 'moyen';
        return 'lent';
    }

    public function calculerScore(int $progression, int $etoiles): int
    {
        return intval(($progression * 0.6) + ($etoiles * 8));
    }

    public function estEligible(int $etoiles, string $rythme): bool
    {
        return $etoiles >= 3 && $rythme === 'bon';
    }

    public function soumettre(
        Onboarding $onboarding,
        User $collaborateur,
        int $etoiles,
        ?string $commentaire
    ): array {
        $duree       = (int) $onboarding->created_at->diffInDays(now());
        // ✅ appel méthode
        $progression = $onboarding->progression();
        $rythme      = $this->calculerRythme($onboarding);
        $score       = $this->calculerScore($progression, $etoiles);
        $eligible    = $this->estEligible($etoiles, $rythme);

        // ✅ validated_by est un int (id) — récupérer le nom
        $validePar = 'RH';
        if ($onboarding->validated_by) {
            $rh = User::find($onboarding->validated_by);
            $validePar = $rh?->name ?? 'RH';
        }

        $avis = Avis::updateOrCreate(
            [
                'onboarding_id'    => $onboarding->id,
                'collaborateur_id' => $collaborateur->id,
            ],
            [
                'etoiles'      => $etoiles,
                'commentaire'  => $commentaire,
                'rythme'       => $rythme,
                'score_sante'  => $score,
                'duree_jours'  => $duree,
                'valide_par'   => $validePar,
                'eligible'     => $eligible,
                'envoye_ia'    => false,
            ]
        );

        if ($eligible) {
            try {
                $this->envoyerAuColab($avis, $onboarding, $collaborateur, $progression, $validePar);
            } catch (\Throwable $e) {
                Log::error('Envoi IA échoué : ' . $e->getMessage());
            }
        }

        return [
            'avis'        => $avis,
            'eligible'    => $eligible,
            'rythme'      => $rythme,
            'score_sante' => $score,
            'message'     => $eligible
                ? "Merci ! Votre retour améliore les futurs onboardings grâce à l'IA."
                : 'Merci pour votre avis !',
        ];
    }

    private function envoyerAuColab(
        Avis $avis,
        Onboarding $onboarding,
        User $collaborateur,
        int $progression,
        string $validePar
    ): void {
        // ✅ plan n'existe pas — on passe les tâches directement
        $etapes = $onboarding->tasks()
            ->select('id', 'title', 'status', 'month_number', 'week_number')
            ->get()
            ->toArray();

        $payload = [
            'plan_id'       => (string) $onboarding->id,
            'collaborateur' => $collaborateur->name ?? '',
            'poste'         => $collaborateur->role?->name ?? '',
            'profil'        => $collaborateur->role?->name ?? '',
            'etoiles'       => $avis->etoiles,
            'commentaire'   => $avis->commentaire ?? '',
            'progression'   => $progression,
            'duree_jours'   => (int) $avis->duree_jours,
            'rythme'        => $avis->rythme,
            'valide_par'    => $validePar,
            'etapes'        => $etapes,
            'score_sante'   => (int) $avis->score_sante,
            'plan_json'     => [
                'onboarding_id' => $onboarding->id,
                'status'        => $onboarding->status,
                'etapes'        => $etapes,
            ],
        ];

        $response = $this->aiService->submitAvis($payload);

        if ($response['success'] ?? false) {
            $avis->update(['envoye_ia' => true]);
            Log::info("Plan #{$onboarding->id} envoyé à la base IA.");
        }
    }

    public function getAvis(int $onboardingId, int $collaborateurId): ?Avis
    {
        return Avis::where('onboarding_id', $onboardingId)
                   ->where('collaborateur_id', $collaborateurId)
                   ->first();
    }

    public function aDejaGiveAvis(int $onboardingId, int $collaborateurId): bool
    {
        return Avis::where('onboarding_id', $onboardingId)
                   ->where('collaborateur_id', $collaborateurId)
                   ->exists();
    }
}