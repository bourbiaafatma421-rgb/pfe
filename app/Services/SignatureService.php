<?php

namespace App\Services;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentAssignment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SignatureService
{
    // ─── Générer un token QR pour le collaborateur ────────────────────────────

    public function genererToken(User $user): string
    {
        $token = Str::random(64);
        $user->update(['signature_token' => $token]);
        return $token;
    }

    // ─── Vérifier le token QR ─────────────────────────────────────────────────

    public function verifierToken(string $token): ?User
    {
        return User::where('signature_token', $token)->first();
    }

    // ─── Enregistrer la signature depuis le canvas (mobile) ───────────────────

    public function enregistrerSignature(string $token, string $signatureBase64): User
    {
        $user = $this->verifierToken($token);

        if (!$user) {
            throw new \Exception("Token invalide ou expiré.");
        }

        // Supprimer ancienne signature si existe
        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // Décoder base64 et sauvegarder
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $signatureBase64));
        $filename  = 'signatures/user_' . $user->id . '_' . time() . '.png';
        Storage::disk('public')->put($filename, $imageData);

        // Mettre à jour le profil
        $user->update([
            'signature_path'  => $filename,
            'signature_token' => null, // invalider le token après usage
        ]);

        return $user;
    }

    // ─── Appliquer la signature sur un document ───────────────────────────────

    public function signerDocument(int $documentId): DocumentSignature
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->signature_path) {
            throw new \Exception("Vous n'avez pas encore enregistré votre signature.");
        }

        $document = Document::findOrFail($documentId);

        // Vérifier que le document est assigné à cet utilisateur
        $assignment = DocumentAssignment::where('document_id', $documentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$assignment) {
            throw new \Exception("Ce document ne vous est pas assigné.");
        }

        if ($assignment->status === 'signed') {
            throw new \Exception("Vous avez déjà signé ce document.");
        }

        // Créer ou mettre à jour la signature du document
        $signature = DocumentSignature::updateOrCreate(
            ['document_id' => $documentId, 'user_id' => $user->id],
            [
                'signature_path' => $user->signature_path,
                'signed_at'      => now(),
                'status'         => 'signed',
            ]
        );

        // Mettre à jour le statut de l'assignment
        $assignment->update(['status' => 'signed']);

        return $signature;
    }

    // ─── Vérifier si le collaborateur a une signature ─────────────────────────

    public function aUneSignature(User $user): bool
    {
        return !empty($user->signature_path);
    }
}