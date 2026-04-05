<?php

namespace App\Http\Controllers\dashboard;

use App\Services\DashboardService;
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
}