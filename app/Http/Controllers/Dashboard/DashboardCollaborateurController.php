<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Services\DashboardCollaborateurService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardCollaborateurController extends Controller
{
    protected DashboardCollaborateurService $dashboardService;

    public function __construct(DashboardCollaborateurService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $this->authorize('view-dashboard'); 

        $user = Auth::user();

        $dashboardData = $this->dashboardService->getCollaboratorDashboardData($user);

        return response()->json($dashboardData);
    }
}
