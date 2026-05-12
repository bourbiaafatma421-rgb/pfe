<?php

namespace App\Services;

use App\Models\Onboarding;
use App\Models\User;

class FormationService
{
    public function getMyFormations(User $user): array
    {
        $onboarding = Onboarding::where('user_id', $user->id)->latest()->first();

        if (!$onboarding) {
            return [
                'formations'       => [],
                'stats'            => $this->emptyStats(),
                'current'          => null,
                'certifications'   => [],
            ];
        }

        $tasks = $onboarding->tasks()
            ->where('type', 'formation')
            ->orderBy('deadline')
            ->get();

        $formations = $tasks->map(fn($task) => [
            'id'        => $task->id,
            'title'     => $task->task_title,
            'status'    => $task->status,
            'deadline'  => $task->deadline?->toDateString(),
            'month'     => $task->month_number,
            'week'      => $task->week_number,
            'day'       => $task->day_name,
            'objective' => $task->objective,
        ])->values()->all();

        // ── Stats ──────────────────────────────────────────────
        $total     = $tasks->count();
        $completed = $tasks->where('status', 'termine')->count();
        $inCours   = $tasks->where('status', 'en_cours')->count();

        $stats = [
            'total'             => $total,
            'completed'         => $completed,
            'in_progress'       => $inCours,
            'modules_completed' => $completed, // 1 module = 1 tâche formation terminée
            'completion_rate'   => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];

        // ── Formation en cours ─────────────────────────────────
        $currentTask = $tasks->firstWhere('status', 'en_cours')
                    ?? $tasks->firstWhere('status', 'en_validation');

        $current = null;
        if ($currentTask) {
            $taskIndex  = $tasks->search(fn($t) => $t->id === $currentTask->id);
            $totalTasks = $tasks->count();

            $current = [
                'id'       => $currentTask->id,
                'title'    => $currentTask->task_title,
                'progress' => $currentTask->status === 'en_validation' ? 80 : 50,
                'module'   => $taskIndex + 1,
                'total'    => $totalTasks,
                'deadline' => $currentTask->deadline?->toDateString(),
            ];
        }

        // ── Certifications (tâches terminées) ─────────────────
        $certifications = $tasks
            ->where('status', 'termine')
            ->map(fn($task) => [
                'id'           => $task->id,
                'title'        => $task->task_title,
                'obtained_at'  => $task->completion_date?->translatedFormat('d F Y')
                               ?? $task->updated_at?->translatedFormat('d F Y'),
                'completed'    => true,
            ])->values()->all();

        // Ajouter les formations en cours dans les certifications (non obtenues)
        $pending = $tasks
            ->whereIn('status', ['en_attente', 'en_cours', 'en_validation'])
            ->map(fn($task) => [
                'id'          => $task->id,
                'title'       => $task->task_title,
                'obtained_at' => null,
                'completed'   => false,
            ])->values()->all();

        return [
            'formations'     => $formations,
            'stats'          => $stats,
            'current'        => $current,
            'certifications' => array_merge($certifications, $pending),
        ];
    }

    private function emptyStats(): array
    {
        return [
            'total'             => 0,
            'completed'         => 0,
            'in_progress'       => 0,
            'modules_completed' => 0,
            'completion_rate'   => 0,
        ];
    }
}