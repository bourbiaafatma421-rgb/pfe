<?php

namespace App\Http\Controllers\Cv;

use App\Http\Controllers\Controller;
use App\Services\CvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CvController extends Controller
{
    protected CvService $service;

    public function __construct(CvService $service)
    {
        $this->service = $service;
    }

    // ─── POST /api/cv/upload ──────────────────────────────────────────────────

    public function upload(Request $request)
    {
        set_time_limit(300);
        $request->validate([
            'cv' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            /** @var \App\Models\User $user */
            $user   = Auth::user();
            $result = $this->service->uploadAndExtract($user, $request->file('cv'));

            return response()->json([
                'message' => 'CV extrait avec succès',
                ...$result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ─── POST /api/cv/validate ────────────────────────────────────────────────

    public function validateCv(Request $request)
    {
        $request->validate([
            'cv_data' => 'required|array',
        ]);

        try {
            /** @var \App\Models\User $user */
            $user   = Auth::user();
            $result = $this->service->saveCvData($user, $request->input('cv_data'));

            return response()->json([
                'message' => 'CV sauvegardé avec succès',
                ...$result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ─── GET /api/cv ──────────────────────────────────────────────────────────

    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return response()->json(
            $this->service->getCvData($user),
            200
        );
    }
}