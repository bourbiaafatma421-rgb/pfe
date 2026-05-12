<?php

namespace App\Http\Controllers\CollaboratteurIA;

use App\Http\Controllers\Controller;
use App\Models\OnboardingTask;
use App\Models\OnboardingTaskComment;
use App\Services\OnboardingCollaborateurService;
use App\Services\FormationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OnboardingCollaborateurController extends Controller
{
    public function __construct(
        protected OnboardingCollaborateurService $onboardingService,
        protected FormationService               $formationService,
    ) {}

    // ────────────────────────────────────────────────────────────
    // GET /api/my/integration-plan
    // ────────────────────────────────────────────────────────────
    public function myPlan(Request $request): JsonResponse
    {
        $plan = $this->onboardingService->getMyPlan($request->user()->id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => "Aucun plan d'intégration trouvé.",
                'data'    => null,
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $plan]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/my/formations
    // ────────────────────────────────────────────────────────────
public function myFormations(Request $request): JsonResponse
{
    $data = $this->formationService->getMyFormations($request->user());

    return response()->json(['success' => true, 'data' => $data]);
}

    // ────────────────────────────────────────────────────────────
    // PATCH /api/my/tasks/{task}
    // ────────────────────────────────────────────────────────────
    public function updateMyTask(Request $request, OnboardingTask $task): JsonResponse
    {
        if (!$this->onboardingService->taskBelongsToUser($task, $request->user()->id)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'status' => ['required', 'in:en_attente,en_cours,en_validation,termine'],
        ]);

        $updated = $this->onboardingService->updateTaskStatus($task, $request->status, $request->user());

        return response()->json([
            'success' => true,
            'data'    => $this->onboardingService->formatTask($updated),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/my/tasks/{task}/comments
    // ────────────────────────────────────────────────────────────
    public function addComment(Request $request, OnboardingTask $task): JsonResponse
    {
        if (!$this->onboardingService->taskBelongsToUser($task, $request->user()->id)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'content'    => ['nullable', 'string', 'max:2000'],
            'link'       => ['nullable', 'url', 'max:500'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if (!$request->filled('content') && !$request->filled('link') && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Un commentaire, un lien ou un fichier est requis.',
            ], 422);
        }

        $comment = $this->onboardingService->addComment(
            $task,
            $request->user()->id,
            $request->content,
            $request->link,
            $request->hasFile('attachment') ? $request->file('attachment') : null
        );

        return response()->json([
            'success' => true,
            'data'    => $this->onboardingService->formatComment($comment->load('user')),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // DELETE /api/my/comments/{comment}
    // ────────────────────────────────────────────────────────────
    public function deleteComment(Request $request, OnboardingTaskComment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $this->onboardingService->deleteComment($comment);

        return response()->json(['success' => true]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/my/tasks/{task}/attachments/{comment}
    // ────────────────────────────────────────────────────────────
    public function downloadAttachment(Request $request, OnboardingTask $task, OnboardingTaskComment $comment)
    {
        $this->onboardingService->authorizeAttachmentAccess($task, $comment, $request->user());

        return $this->onboardingService->streamAttachment($comment);
    }
}