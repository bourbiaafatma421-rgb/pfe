<?php

namespace App\Services;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentAssignment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;

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
            'signature_token' => null,
        ]);

        return $user;
    }

    // ─── Appliquer la signature sur un document PDF ───────────────────────────

    public function signerDocument(int $documentId): DocumentAssignment
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->signature_path) {
            throw new \Exception("Vous n'avez pas encore enregistré votre signature.");
        }

        $document = Document::findOrFail($documentId);

        $assignment = DocumentAssignment::where('document_id', $documentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$assignment) {
            throw new \Exception("Ce document ne vous est pas assigné.");
        }

        if ($assignment->status === 'signed') {
            throw new \Exception("Vous avez déjà signé ce document.");
        }

        // ── Chemins fichiers ──────────────────────────────────────────────────
        $pdfPath        = Storage::disk('public')->path($document->path);
        $signaturePath  = Storage::disk('public')->path($user->signature_path);
        $outputFilename = 'documents/signed_' . $documentId . '_user_' . $user->id . '_' . time() . '.pdf';
        $outputPath     = Storage::disk('public')->path($outputFilename);

        // ── Créer dossier si besoin ───────────────────────────────────────────
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // ── Apposer la signature sur le PDF ───────────────────────────────────
        $this->apposSignatureSurPdf($pdfPath, $signaturePath, $outputPath);

        // ── Mettre à jour l'assignment ────────────────────────────────────────
        $assignment->update([
            'status'         => 'signed',
            'signed_at'      => now(),
            'signature_path' => $outputFilename,
        ]);

        return $assignment;
    }

    // ─── Apposer la signature PNG sur la dernière page du PDF ─────────────────

    private function apposSignatureSurPdf(string $pdfPath, string $signaturePath, string $outputPath): void
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);

            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

            // Apposer signature uniquement sur la dernière page
            if ($i === $pageCount) {
                $sigWidth  = 55;  // largeur en mm
                $sigHeight = 18;  // hauteur en mm
                $x = $size['width'] - $sigWidth - 15;    // coin bas droite
                $y = $size['height'] - $sigHeight - 20;

                // Cadre autour de la signature
                $pdf->SetDrawColor(180, 180, 180);
                $pdf->Rect($x - 2, $y - 2, $sigWidth + 4, $sigHeight + 10);

                // Label "Signé par"
                $pdf->SetFont('Helvetica', 'I', 7);
                $pdf->SetTextColor(120, 120, 120);
                $pdf->SetXY($x, $y - 6);
                $pdf->Cell($sigWidth, 4, 'Signature électronique', 0, 0, 'C');

                // Image signature
                $pdf->Image($signaturePath, $x, $y, $sigWidth, $sigHeight, 'PNG');

                // Date et heure
                $pdf->SetFont('Helvetica', '', 6);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetXY($x, $y + $sigHeight + 1);
                $pdf->Cell($sigWidth, 4, 'Signé le ' . now()->format('d/m/Y à H:i'), 0, 0, 'C');
            }
        }

        $pdf->Output('F', $outputPath);
    }

    // ─── Vérifier si le collaborateur a une signature ─────────────────────────

    public function aUneSignature(User $user): bool
    {
        return !empty($user->signature_path);
    }
}