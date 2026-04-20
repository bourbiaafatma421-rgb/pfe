<?php

namespace App\Http\Controllers\IntegrationPlan;
use App\Http\Controllers\Controller; // ← obligatoire
use Illuminate\Support\Facades\Gate; // ← important

use App\Services\IntegrationPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationPlanController extends Controller
{
    public function __construct(private IntegrationPlanService $service) {}

    public function myPlan(Request $request): JsonResponse
    {
        Gate::authorize('view-integration-plan');
        $user = $request->user();

        return response()->json(
            $this->service->getByUser($user)
        );
    }
}