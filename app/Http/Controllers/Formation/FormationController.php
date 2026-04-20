<?php

namespace App\Http\Controllers\Formation;

use App\Http\Controllers\Controller; // ← il manquait ça
use App\Services\FormationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Gate; // ← important

class FormationController extends Controller
{
    public function __construct(private FormationService $service) {}

    public function myFormations(Request $request): JsonResponse
    {
        Gate::authorize('view-formations');

        $user = $request->user();

        $formations = $this->service->getByUser($user);

        return response()->json($formations);
    }
}