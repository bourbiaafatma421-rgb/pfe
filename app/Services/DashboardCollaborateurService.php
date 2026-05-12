<?php

namespace App\Services;

use App\Models\User;
use App\Models\DocumentAssignment;
use App\Models\Onboarding;
use App\Models\OnboardingTask;
use Carbon\Carbon;

class DashboardCollaborateurService
{
    public function getCollaboratorDashboardData(User $user): array
    {
        // ── Documents (inchangé, déjà dynamique) ──────────────────
        $documents = DocumentAssignment::with('document')
            ->where('user_id', $user->id)
            ->get();

        $documentsTotal     = $documents->count();
        $documentsCompleted = $documents->where('status', 'signed')->count();
        $documentsProgress  = $documentsTotal > 0
            ? round(($documentsCompleted / $documentsTotal) * 100) : 0;

        $documentsList = $documents->map(fn($doc) => [
            'name'   => optional($doc->document)->namedoc ?? 'Document',
            'status' => strtolower($doc->status),
        ]);

        // ── Formations : on lit les vraies OnboardingTask ──────────
        $onboarding = Onboarding::where('user_id', $user->id)->first();

        $formationsTotal     = 0;
        $formationsCompleted = 0;
        $formationsProgress  = 0;
        $overallProgress     = 0;

        if ($onboarding) {
            // "formations" = tâches de type formation dans le plan
            $tachesFormation = $onboarding->tasks()
                ->where('type', 'formation')
                ->get();

            $formationsTotal     = $tachesFormation->count();
            $formationsCompleted = $tachesFormation->where('status', 'termine')->count();
            $formationsProgress  = $formationsTotal > 0
                ? round(($formationsCompleted / $formationsTotal) * 100) : 0;

            // overall_progress = méthode existante sur le modèle Onboarding
            // (toutes les tâches, pas juste formations)
            $overallProgress = $onboarding->progression();
        }

        // ── Jours restants ─────────────────────────────────────────
        $integrationEnd = $user->integration_end_date ?? Carbon::now()->addDays(30);
        $daysRemaining  = max(0, Carbon::now()->diffInDays($integrationEnd, false));

        // ── Activités récentes (dynamiques) ───────────────────────
        $recentActivities = $this->getRecentActivities($user, $onboarding);

        // ── Événements à venir (tâches futures non terminées) ──────
        $upcomingEvents = $this->getUpcomingEvents($onboarding);

        return [
            'user' => [
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
            ],
            'documents' => [
                'total'     => $documentsTotal,
                'completed' => $documentsCompleted,
                'progress'  => $documentsProgress,
                'list'      => $documentsList,
            ],
            'formations' => [
                'total'     => $formationsTotal,
                'completed' => $formationsCompleted,
                'progress'  => $formationsProgress,
            ],
            'overall_progress' => $overallProgress,
            'days_remaining'   => $daysRemaining,
            'recent_activities' => $recentActivities,
            'upcoming_events'   => $upcomingEvents,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    private function getRecentActivities(User $user, ?Onboarding $onboarding): array
    {
        $activities = [];

        // Documents récents
        $documents = DocumentAssignment::with('document')
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($documents as $doc) {
            $activities[] = [
                'type'        => 'Document',
                'description' => optional($doc->document)->namedoc ?? 'Document',
                'date'        => $doc->updated_at->format('d M Y'),
                'time'        => $doc->updated_at->format('H:i'),
                'status'      => $doc->status === 'signed' ? 'signed' : 'pending',
            ];
        }

        // Tâches récemment modifiées dans le plan
        if ($onboarding) {
            $recentTasks = $onboarding->tasks()
                ->whereNotNull('updated_at')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentTasks as $task) {
                $activities[] = [
                    'type'        => ucfirst($task->type ?? 'Tâche'),
                    'description' => $task->task_title,
                    'date'        => $task->updated_at->format('d M Y'),
                    'time'        => $task->updated_at->format('H:i'),
                    'status'      => match ($task->status) {
                        'termine'       => 'Complété',
                        'en_validation' => 'En validation',
                        'en_cours'      => 'En cours',
                        default         => 'En attente',
                    },
                ];
            }
        }

        return collect($activities)
            ->sortByDesc(fn($a) => strtotime($a['date'] . ' ' . $a['time']))
            ->take(5)
            ->values()
            ->all();
    }

    // ─────────────────────────────────────────────────────────────
    private function getUpcomingEvents(?Onboarding $onboarding): array
{
    if (!$onboarding) return [];

    return $onboarding->tasks()
        ->whereNotIn('status', ['termine', 'rejetee'])
        ->whereNotNull('deadline')
        ->where('deadline', '>=', Carbon::today())
        ->orderBy('deadline')
        // ← limit(5) supprimé : on retourne tout
        ->get()
        ->map(fn($task) => [
            'id'    => $task->id,
            'title' => $task->task_title,
            'date'  => Carbon::parse($task->deadline)->format('d M Y'),
            'time'  => '09:00',
            'type'  => ucfirst($task->type ?? 'Tâche'),
        ])
        ->values()
        ->all();
}
}