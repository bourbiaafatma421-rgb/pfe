<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class CvService
{
    // ─── Upload PDF + extraction via Flask ────────────────────────────────────

    public function uploadAndExtract(User $user, UploadedFile $file): array
    {
        $flaskUrl = config('app.flask_url', 'http://127.0.0.1:5000');

        $response = Http::timeout(1400)
            ->withOptions([
                'curl' => [
                    CURLOPT_TIMEOUT => 1400,
                    CURLOPT_CONNECTTIMEOUT => 30,
                ]
            ])
            ->attach('pdf', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("{$flaskUrl}/extract");
        if (!$response->successful()) {
            Log::error('Flask CV extraction error', ['response' => $response->body()]);
            throw new \Exception('Erreur extraction IA : ' . $response->body());
        }

        return [
            'cv_data' => $response->json(),
        ];
    }

    // ─── Valider et sauvegarder cv_data ──────────────────────────────────────

    public function saveCvData(User $user, array $cvData): array
    {
        $user->update(['cv_data' => $cvData]);

        return [
            'cv_data' => $user->fresh()->cv_data,
        ];
    }

    // ─── Récupérer cv_data ────────────────────────────────────────────────────

    public function getCvData(User $user): array
    {
        return [
            'cv_data' => $user->cv_data,
        ];
    }
}