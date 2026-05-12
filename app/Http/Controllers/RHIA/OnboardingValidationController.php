<?php

namespace App\Http\Controllers\RHIA;

use App\Http\Controllers\Controller;
use App\Models\OnboardingTask;
use App\Notifications\TaskRejected;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingValidationController extends Controller
{
    // ── POST /api/rh/tasks/{task}/approve ────────────────────────────────────

    public function approve(OnboardingTask $task): JsonResponse
    {
        if ($task->status !== 'en_validation') {
            return response()->json([
                'success' => false,
                'message' => 'Tâche non en attente de validation.',
            ], 422);
        }

        $task->update([
            'status'           => 'termine',
            'completion_date'  => now()->toDateString(),
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $task->id,
                'status'    => 'termine',
                'completed' => true,
            ],
        ]);
    }

    // ── POST /api/rh/tasks/{task}/reject ─────────────────────────────────────

    public function reject(Request $request, OnboardingTask $task): JsonResponse
    {
        if ($task->status !== 'en_validation') {
            return response()->json([
                'success' => false,
                'message' => 'Tâche non en attente de validation.',
            ], 422);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $task->update([
            'status'           => 'en_cours',
            'completion_date'  => null,
            'rejection_reason' => $request->reason,
        ]);

        // Notifier le collaborateur
        $collaborateur = $task->onboarding->user;
        if ($collaborateur) {
            $collaborateur->notify(new TaskRejected($task, $request->reason));
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $task->id,
                'status'           => 'en_cours',
                'rejection_reason' => $request->reason,
                'completed'        => false,
            ],
        ]);
    }
}