<?php

namespace App\Http\Controllers;

use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class SignatureController extends BaseController
{
    protected SignatureService $service;

    public function __construct(SignatureService $service)
    {
        $this->service = $service;
    }

    // ─── GET /api/signature/token ─────────────────────────────────────────────
    // Génère un token QR pour le collaborateur connecté

    public function genererToken(Request $request)
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $token = $this->service->genererToken($user);

        return response()->json([
            'message' => 'Token généré avec succès',
            'token'   => $token,
            'url'     => config('app.frontend_url') . '/sign/' . $token,
        ], 200);
    }

    // ─── GET /api/sign/{token} ────────────────────────────────────────────────
    // Vérifie le token (appelé depuis la page mobile)

    public function verifierToken(string $token)
    {
        $user = $this->service->verifierToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token invalide ou expiré'], 404);
        }

        return response()->json([
            'message'    => 'Token valide',
            'user'       => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'has_signature' => !empty($user->signature_path),
            ],
        ], 200);
    }

    // ─── POST /api/sign/{token} ───────────────────────────────────────────────
    // Enregistre la signature depuis le mobile (route publique)

    public function enregistrerSignature(Request $request, string $token)
    {
        $request->validate([
            'signature' => 'required|string', // base64 image
        ]);

        try {
            $user = $this->service->enregistrerSignature($token, $request->input('signature'));

            return response()->json([
                'message' => 'Signature enregistrée avec succès',
                'user_id' => $user->id,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ─── POST /api/signature/signer/{documentId} ──────────────────────────────
    // Applique la signature du collaborateur sur un document (route protégée)

    public function signerDocument(int $documentId)
    {
        try {
            $signature = $this->service->signerDocument($documentId);

            return response()->json([
                'message'   => 'Document signé avec succès',
                'signature' => [
                    'document_id'    => $signature->document_id,
                    'user_id'        => $signature->user_id,
                    'signature_path' => asset('storage/' . $signature->signature_path),
                    'signed_at'      => $signature->signed_at,
                    'status'         => $signature->status,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ─── GET /api/signature/status ────────────────────────────────────────────
    // Vérifie si le collaborateur connecté a une signature

    public function statut()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return response()->json([
            'has_signature'  => $this->service->aUneSignature($user),
            'signature_path' => $user->signature_path
                ? asset('storage/' . $user->signature_path)
                : null,
        ], 200);
    }
}