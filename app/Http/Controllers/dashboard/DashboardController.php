<?php

namespace App\Http\Controllers\dashboard;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    // GET /api/dashboard/stats
    public function stats()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Seuls manager et RH peuvent voir le dashboard
        if (!$user->hasRole('manager') && !$user->hasRole('rh')) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $data = $this->service->getDashboardData();

        return response()->json([
            'message' => 'Dashboard data récupéré avec succès',
            ...$data,
        ], 200);
    }
    // GET /api/dashboard/onboarding-progress?page=1&per_page=5
public function onboardingProgress(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if (!$user->hasRole('manager') && !$user->hasRole('rh')) {
        return response()->json(['message' => 'Accès refusé'], 403);
    }

    $page    = max(1, (int) $request->get('page', 1));
    $perPage = min((int) $request->get('per_page', 5), 50);

    $data = $this->service->getOnboardingProgress($page, $perPage);

    return response()->json($data, 200);
}
}