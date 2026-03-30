<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends BaseController
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    //GET 

    public function stats(){
        $this->authorize('view-dashboard');
        $data = $this->service->getDashboardData();
        return response()->json([
            'message' => 'Dashboard data récupéré avec succès',
            ...$data,
        ], 200);
    }
}