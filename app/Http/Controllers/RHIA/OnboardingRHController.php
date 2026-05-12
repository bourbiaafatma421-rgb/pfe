<?php

namespace App\Http\Controllers\RHIA;

use App\Http\Controllers\Controller;
use App\Models\Onboarding;
use App\Models\OnboardingTask;
use App\Models\User;
use App\Services\OnboardingAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnboardingRHController extends Controller
{
    public function __construct(private OnboardingAIService $aiService) {}

    // ── Liste tous les onboardings ────────────────────────
    public function index()
    {
        $this->authorize('viewAny', Onboarding::class);

        $onboardings = Onboarding::with(['user', 'validatedBy'])
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id'               => $o->id,
                'collaborateur'    => $o->user->first_name . ' ' . $o->user->last_name,
                'status'           => $o->status,
                'progression'      => $o->progression(),
                'validated_by'     => $o->validatedBy?->first_name . ' ' . $o->validatedBy?->last_name,
                'validated_at'     => $o->validated_at?->toDateTimeString(),
                'validation_notes' => $o->validation_notes,
                'created_at'       => $o->created_at->toDateString(),
            ]);

        return response()->json(['success' => true, 'data' => $onboardings]);
    }

    // ── Détail d'un onboarding ────────────────────────────
    public function show(Onboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        return response()->json([
            'success'     => true,
            'onboarding'  => $onboarding->load(['tasks', 'user', 'validatedBy']),
            'progression' => $onboarding->progression(),
        ]);
    }

    // ── Générer le plan IA ────────────────────────────────
    public function generer(Request $request, User $user)
    {
        $this->authorize('generate', Onboarding::class);

        $request->validate([
            'poste'        => 'required|string|max:255',
            'description'  => 'required|string',
            'months_count' => 'required|integer|min:1|max:12',
        ]);

        try {
            $onboarding = $this->aiService->genererPlan(
                $user,
                $request->poste,
                $request->description,
                $request->months_count,
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Plan généré avec succès.',
                'onboarding' => $onboarding,
            ]);

        } catch (\Exception $e) {
            Log::error('OnboardingRH: erreur génération', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Valider un onboarding ─────────────────────────────
    public function valider(Request $request, Onboarding $onboarding)
    {
        $this->authorize('valider', $onboarding);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $onboarding->valider($request->user(), $request->notes);

        return response()->json([
            'success'    => true,
            'message'    => 'Onboarding validé avec succès.',
            'onboarding' => $onboarding->fresh(['validatedBy']),
        ]);
    }

    // ── Modifier une tâche ────────────────────────────────
    public function updateTask(Request $request, OnboardingTask $task)
    {
        $this->authorize('updateTask', Onboarding::class);

        $request->validate([
            'task_title' => 'sometimes|string|max:500',
            'objective'  => 'sometimes|nullable|string',
            'deadline'   => 'sometimes|date',
            'type'       => ['sometimes', 'in:technique,administratif,humain,formation'], 
            'status'     => 'sometimes|in:en_attente,en_cours,termine',
        ]);

        $task->update($request->only([
            'task_title', 'objective', 'deadline', 'type', 'status',
        ]));

        if ($request->status === 'termine' && !$task->completion_date) {
            $task->update(['completion_date' => now()->toDateString()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tâche mise à jour.',
            'task'    => $task->fresh(),
        ]);
    }

    // ── Ajouter une tâche ─────────────────────────────────
    public function addTask(Request $request, Onboarding $onboarding)
    {
        $this->authorize('addTask', Onboarding::class);

        $request->validate([
            'task_title'   => 'required|string|max:500',
            'objective'    => 'nullable|string',
            'deadline'     => 'required|date',
            'type'         => 'required|in:technique,administratif,humain',
            'month_number' => 'required|integer|min:1',
            'week_number'  => 'required|integer|min:1',
            'day_name'     => 'nullable|in:lundi,mardi,mercredi,jeudi,vendredi',
        ]);

        $task = $onboarding->tasks()->create([
            ...$request->only([
                'task_title', 'objective', 'deadline',
                'type', 'month_number', 'week_number', 'day_name',
            ]),
            'status' => 'en_attente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tâche ajoutée.',
            'task'    => $task,
        ], 201);
    }

    // ── Supprimer une tâche ───────────────────────────────
    public function deleteTask(OnboardingTask $task)
    {
        $this->authorize('deleteTask', Onboarding::class);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tâche supprimée.',
        ]);
    }

    // ── Status Colab ──────────────────────────────────────
    public function colabStatus()
    {
        $this->authorize('viewAny', Onboarding::class);

        $online = $this->aiService->isColabOnline();

        return response()->json([
            'success' => true,
            'online'  => $online,
            'message' => $online ? 'Colab est en ligne.' : 'Colab est hors ligne.',
        ]);
    }
    
}