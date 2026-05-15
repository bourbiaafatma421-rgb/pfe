<?php

namespace App\Http\Controllers\RHIA;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOnboardingTaskRequest;
use App\Http\Requests\UpdateOnboardingTaskRequest;
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

        $onboarding->load([
            'tasks' => fn($q) => $q->orderBy('deadline', 'asc'),  // ← ajouter
            'tasks.responsable',
            'tasks.comments',
            'user',
            'validatedBy'
        ]);

        return response()->json([
            'success'     => true,
            'onboarding'  => $onboarding,
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
    public function updateTask(UpdateOnboardingTaskRequest $request, OnboardingTask $task)
    {
        $this->authorize('updateTask', Onboarding::class);

        $task->update($request->only([
            'task_title', 'objective', 'deadline', 'type',
            'status', 'rejection_reason', 'responsable_id',
        ]));

        if ($request->status === 'termine' && !$task->completion_date) {
            $task->update(['completion_date' => now()->toDateString()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tâche mise à jour.',
            'task'    => $task->fresh(['responsable']),
        ]);
    }

    // ── Ajouter une tâche ─────────────────────────────────
    public function addTask(StoreOnboardingTaskRequest $request, Onboarding $onboarding)
    {
        $this->authorize('addTask', Onboarding::class);

        $task = $onboarding->tasks()->create([
            ...$request->only([
                'task_title', 'objective', 'deadline',
                'type', 'month_number', 'week_number',
                'day_name', 'responsable_id',
            ]),
            'status' => 'en_attente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tâche ajoutée.',
            'task'    => $task->load('responsable'),
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

    // ── Liste des responsables disponibles ────────────────
    public function getResponsables()
{
    $this->authorize('viewAny', Onboarding::class);

    $responsables = User::where('active', true)
        ->whereHas('role', function($q) {
            $q->where('name', '!=', 'new_collaborateur');
        })
        ->select('id', 'first_name', 'last_name', 'role_id')
        ->with('role:id,name')
        ->orderBy('first_name')
        ->get();

    return response()->json([
        'success'      => true,
        'responsables' => $responsables,
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