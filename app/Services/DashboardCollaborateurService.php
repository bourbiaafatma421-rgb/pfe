<?php

namespace App\Services;

use App\Models\User;
use App\Models\DocumentAssignment;
use Carbon\Carbon;
use App\Models\Document;

class DashboardCollaborateurService
{

    public function getCollaboratorDashboardData(User $user)
    {

        $documents = DocumentAssignment::with('document')
        ->where('user_id', $user->id)
        ->get();

        $documentsTotal = $documents->count();

        $documentsCompleted = $documents->filter(function ($doc) {
            return strtolower($doc->status) === 'signed';
        })->count();

        $documentsProgress = $documentsTotal > 0
            ? round(($documentsCompleted / $documentsTotal) * 100)
            : 0;

        $documentsList = $documents->map(function ($doc) {
            return [
                'name' => optional($doc->document)->namedoc ?? 'Document',
                'status' => strtolower($doc->status), 
            ];
        });

            $formations = [
            ['title' => 'Module Sécurité IT', 'status' => 'En cours'],
            ['title' => 'CRM Avancé', 'status' => 'En cours'],
            ['title' => 'Présentation projet équipe', 'status' => 'Complété'],
        ];

        $formationsTotal = count($formations);
        $formationsCompleted = collect($formations)
            ->where('status', 'Complété')
            ->count();

        $formationsProgress = $formationsTotal > 0
            ? round(($formationsCompleted / $formationsTotal) * 100)
            : 0;

        $overallProgress = ($documentsTotal + $formationsTotal) > 0
            ? round((($documentsCompleted + $formationsCompleted) / ($documentsTotal + $formationsTotal)) * 100)
            : 0;


        $integrationEnd = $user->integration_end_date ?? Carbon::now()->addDays(30);
        $daysRemaining = Carbon::now()->diffInDays($integrationEnd, false);


        $recentActivities = $this->getRecentActivities($user, $formations);

        $upcomingEvents = [
            ['title' => 'Entretien RH trimestriel', 'date' => '05 Avril 2026', 'time' => '14:00', 'type' => 'Réunion'],
            ['title' => 'Formation CRM Avancé', 'date' => '08 Avril 2026', 'time' => '09:00', 'type' => 'Formation'],
            ['title' => 'Présentation projet équipe', 'date' => '12 Avril 2026', 'time' => '10:30', 'type' => 'Présentation'],
        ];

        return [
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
            'documents' => [
                'total' => $documentsTotal,
                'completed' => $documentsCompleted,
                'progress' => $documentsProgress,
                'list' => $documentsList,
            ],
            'formations' => [
                'total' => $formationsTotal,
                'completed' => $formationsCompleted,
                'progress' => $formationsProgress,
            ],
            'overall_progress' => $overallProgress,
            'days_remaining' => $daysRemaining,
            'recent_activities' => $recentActivities,
            'upcoming_events' => $upcomingEvents,
        ];
    }


    private function getRecentActivities(User $user, array $formations)
    {
        $activities = [];

        $documents = DocumentAssignment::with('document')
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($documents as $doc) {
            $activities[] = [
                'type' => 'Document',
                'description' => $doc->document->namedoc ?? 'Document', 
                'date' => $doc->updated_at->format('d M Y'),
                'time' => $doc->updated_at->format('H:i'),
                'status' => $doc->status === 'signed' ? 'signed' : 'pending',
            ];
        }

        foreach ($formations as $f) {
            $activities[] = [
                'type' => 'Formation',
                'description' => $f['title'],
                'date' => Carbon::now()->subDays(rand(0, 10))->format('d M Y'),
                'time' => Carbon::now()->subMinutes(rand(10, 120))->format('H:i'),
                'status' => $f['status'],
            ];
        }

        return collect($activities)
            ->sortByDesc(function ($item) {
                return strtotime($item['date'] . ' ' . $item['time']);
            })
            ->take(5)
            ->values()
            ->all();
    }
}