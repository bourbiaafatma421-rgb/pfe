<?php

namespace App\Http\Controllers\CollaboratteurIA;

use App\Http\Controllers\Controller;
use App\Models\Onboarding;
use App\Services\AvisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvisController extends Controller
{
    public function __construct(
        private AvisService $avisService
    ) {}

    public function store(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'etoiles'     => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $collaborateur = Auth::user();

        $onboarding = Onboarding::where('id', $id)
                                ->where('user_id', $collaborateur->id)
                                ->firstOrFail();

        // Vérification manuelle — pas de policy sur Onboarding
        if (Auth::id() !== $onboarding->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.',
            ], 403);
        }

        if ($this->avisService->aDejaGiveAvis($id, $collaborateur->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà soumis un avis pour ce plan.',
            ], 422);
        }

        $result = $this->avisService->soumettre(
            onboarding:    $onboarding,
            collaborateur: $collaborateur,
            etoiles:       $request->etoiles,
            commentaire:   $request->commentaire,
        );

        return response()->json([
            'success'     => true,
            'eligible'    => $result['eligible'],
            'rythme'      => $result['rythme'],
            'score_sante' => $result['score_sante'],
            'message'     => $result['message'],
            'avis'        => $result['avis'],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $collaborateur = Auth::user();

        // La vérification user_id suffit — pas besoin de policy
        Onboarding::where('id', $id)
                  ->where('user_id', $collaborateur->id)
                  ->firstOrFail();

        $avis = $this->avisService->getAvis($id, $collaborateur->id);

        return response()->json([
            'success'       => true,
            'avis'          => $avis,
            'a_deja_soumis' => !is_null($avis),
        ]);
    }
}