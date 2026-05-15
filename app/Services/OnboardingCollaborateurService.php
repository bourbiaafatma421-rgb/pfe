<?php

namespace App\Services;

use App\Models\Onboarding;
use App\Models\OnboardingTask;
use App\Models\OnboardingTaskComment;
use App\Models\User;
use App\Notifications\TaskEnValidationNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OnboardingCollaborateurService
{
    // ────────────────────────────────────────────────────────────
    // Autorisations
    // ────────────────────────────────────────────────────────────

    public function taskBelongsToUser(OnboardingTask $task, int $userId): bool
    {
        return $task->onboarding->user_id === $userId;
    }

    public function authorizeAttachmentAccess(
        OnboardingTask        $task,
        OnboardingTaskComment $comment,
        User                  $user
    ): void {
        $canAccess = $task->onboarding->user_id === $user->id
                  || $comment->user_id          === $user->id;

        if (!$canAccess) {
            throw new AccessDeniedHttpException();
        }
    }

    // ────────────────────────────────────────────────────────────
    // Plan d'intégration
    // ────────────────────────────────────────────────────────────

  public function getMyPlan(int $userId): ?array{
    $onboarding = Onboarding::with(['tasks.comments.user', 'tasks.responsable']) // ← ajouter
        ->where('user_id', $userId)
        ->latest()
        ->first();

    return $onboarding ? $this->formatPlan($onboarding) : null;
}


    // ────────────────────────────────────────────────────────────
    // Tâches
    // ────────────────────────────────────────────────────────────

    public function updateTaskStatus(OnboardingTask $task, string $status, User $actor): OnboardingTask
    {
        $task->update(['status' => $status]);

        if ($status === 'en_validation') {
            $this->notifyResponsable($task, $actor);
        }

        return $task->fresh();
    }

    private function notifyResponsable(OnboardingTask $task, User $actor): void
    {
        $responsable = $task->onboarding->user->responsable ?? null;

        if ($responsable) {
            $responsable->notify(new TaskEnValidationNotification($task, $actor));
        }
    }

    // ────────────────────────────────────────────────────────────
    // Commentaires
    // ────────────────────────────────────────────────────────────

    public function addComment(
        OnboardingTask $task,
        int            $userId,
        ?string        $content,
        ?string        $link,
        ?UploadedFile  $attachment
    ): OnboardingTaskComment {
        $data = [
            // FIX : la FK dans OnboardingTaskComment est 'onboarding_task_id'
            'onboarding_task_id' => $task->id,
            'user_id'            => $userId,
            'content'            => $content,
            'link'               => $link,
        ];

        if ($attachment) {
            $path = $attachment->store('task-attachments', 'private');

            $data['attachment_path'] = $path;
            $data['attachment_name'] = $attachment->getClientOriginalName();
            $data['attachment_mime'] = $attachment->getMimeType();
        }

        return OnboardingTaskComment::create($data);
    }

    public function deleteComment(OnboardingTaskComment $comment): void
    {
        if ($comment->attachment_path) {
            Storage::disk('private')->delete($comment->attachment_path);
        }

        $comment->delete();
    }

    // ────────────────────────────────────────────────────────────
    // Pièces jointes
    // ────────────────────────────────────────────────────────────

    public function streamAttachment(OnboardingTaskComment $comment): BinaryFileResponse
    {
        if (!$comment->attachment_path || !Storage::disk('private')->exists($comment->attachment_path)) {
            throw new NotFoundHttpException('Fichier introuvable.');
        }

        return response()->download(
            storage_path('app/private/' . $comment->attachment_path),
            $comment->attachment_name ?? 'fichier',
            ['Content-Type' => $comment->attachment_mime ?? 'application/octet-stream']
        );
    }

    // ────────────────────────────────────────────────────────────
    // Formatters
    // ────────────────────────────────────────────────────────────

    public function formatTask(OnboardingTask $task): array
{
    return [
        'id'               => $task->id,
        'title'            => $task->task_title,
        'status'           => $task->status,
        'completed'        => $task->status === 'termine',
        'due_date'         => $task->deadline?->toDateString(),
        'day_name'         => $task->day_name,
        'week_number'      => $task->week_number,
        'rejection_reason' => $task->rejection_reason,
        'type'             => $task->type,
        'comments'         => $task->relationLoaded('comments')
            ? $task->comments->map(fn($c) => $this->formatComment($c))->values()->all()
            : [],
        'responsable_id'   => $task->responsable_id,
        'responsable'      => $task->relationLoaded('responsable') && $task->responsable
            ? [
                'id'         => $task->responsable->id,
                'first_name' => $task->responsable->first_name,
                'last_name'  => $task->responsable->last_name,
            ]
            : null,
    ];
}

    public function formatComment(OnboardingTaskComment $comment): array
    {
        return [
            'id'              => $comment->id,
            'content'         => $comment->content,
            'link'            => $comment->link,
            // FIX : champs attendus par le frontend (interface TaskComment)
            'has_attachment'  => $comment->hasAttachment(),
            'attachment_name' => $comment->attachment_name,
            'attachment_mime' => $comment->attachment_mime,
            'download_url'    => $comment->hasAttachment()
                ? url("/api/my/tasks/{$comment->task->onboarding_id}/attachments/{$comment->id}")
                : null,
            'created_at'      => $comment->created_at->toDateTimeString(),
            'author'          => $comment->relationLoaded('user') ? [
                'id'   => $comment->user->id,
                'name' => $comment->user->name,
            ] : null,
        ];
    }

    private function formatPlan(Onboarding $onboarding): array
    {
        // Grouper les tâches par mois pour construire les phases
        $phases = $onboarding->tasks
            ->groupBy('month_number')
            ->map(function ($tasks, $month) {
                $tasksFormatted = $tasks->map(fn($t) => $this->formatTask($t))->values()->all();
                $total          = count($tasksFormatted);
                $done           = collect($tasksFormatted)->where('completed', true)->count();
                $progress       = $total > 0 ? (int) round(($done / $total) * 100) : 0;
                $status         = $progress === 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');

                return [
                    'phase'      => "Mois {$month}",
                    'title'      => "Mois {$month}",
                    'status'     => $status,
                    'progress'   => $progress,
                    'start_date' => $tasks->min('deadline'),
                    'end_date'   => $tasks->max('deadline'),
                    'tasks'      => $tasksFormatted,
                ];
            })
            ->sortKeys()
            ->values()
            ->all();

        $total    = $onboarding->tasks->count();
        $termine  = $onboarding->tasks->where('status', 'termine')->count();
        $progress = $total > 0 ? (int) round(($termine / $total) * 100) : 0;

        return [
            'id'             => $onboarding->id,
            'user_id'        => $onboarding->user_id,
            'start_date'     => $onboarding->tasks->min('deadline'),
            'end_date'       => $onboarding->tasks->max('deadline'),
            'jours_left'     => 0,
            'progression'    => $progress,
            'phases'         => $phases,
            'meetings'       => [],
            'action_requise' => null,
        ];
    }
    // Ajouter cette méthode après getMyPlan()
    public function getMesSuivis(int $userId): array{
        $tasks = OnboardingTask::with(['onboarding.user', 'comments.user', 'responsable'])
            ->where('responsable_id', $userId)
            ->orderBy('deadline', 'asc')
            ->get();

        return $tasks->map(function ($task) {
            return [
                'id'           => $task->id,
                'title'        => $task->task_title,
                'status'       => $task->status,
                'due_date'     => $task->deadline?->toDateString(),
                'day_name'     => $task->day_name,
                'type'         => $task->type,
                'onboarding_id' => $task->onboarding_id,
                'collaborateur' => $task->onboarding->user
                    ? $task->onboarding->user->first_name . ' ' . $task->onboarding->user->last_name
                    : '—',
                'comments_count' => $task->comments->count(),
                'comments'     => $task->comments->map(fn($c) => $this->formatComment($c))->values()->all(),
            ];
        })->all();
    }
}