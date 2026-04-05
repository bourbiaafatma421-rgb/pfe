<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAssignment;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // ─── Rôles exclus des stats générales (graphique barres + activité) ───────
    private array $excludedFromStats = ['manager'];

    // ─── Rôles exclus du pie chart (management uniquement) ───────────────────
    private array $excludedFromPie = ['manager'];

    // ─── Labels lisibles pour les rôles techniques ───────────────────────────
    private array $roleLabels = [
        'rh'                   => 'RH',
        'manager'              => 'Manager',
        'designer'             => 'Designer',
        'comptable'            => 'Comptable',
        'développeur backend'  => 'Dev Backend',
        'développeur frontend' => 'Dev Frontend',
    ];

    // ─── Stats principales ────────────────────────────────────────────────────

    public function getStats(): array
    {
        $query = User::query()->whereHas('role', function ($q) {
            $q->whereNotIn(DB::raw('LOWER(name)'), $this->excludedFromStats);
        });

        $totalCollaborateurs    = $query->count();
        $collaborateursActifs   = (clone $query)->where('active', true)->count();
        $collaborateursInactifs = (clone $query)->where('active', false)->count();

        $nouveauxCeMois = (clone $query)
            ->whereYear('date_of_hire', Carbon::now()->year)
            ->whereMonth('date_of_hire', Carbon::now()->month)
            ->count();

        $documentsEnAttente = Document::where('signature_req', true)->count();

        return [
            'total_collaborateurs'    => $totalCollaborateurs,
            'collaborateurs_actifs'   => $collaborateursActifs,
            'collaborateurs_inactifs' => $collaborateursInactifs,
            'documents_en_attente'    => $documentsEnAttente,
            'nouveaux_ce_mois'        => $nouveauxCeMois,
        ];
    }

    // ─── Répartition par rôle ─────────────────────────────────────────────────

    public function getRepartitionRoles(): array
    {
        $roles = Role::select('roles.id', 'roles.name')
            ->selectRaw('COUNT(users.id) as users_count')
            ->leftJoin('users', 'users.role_id', '=', 'roles.id')
            ->whereNotIn(DB::raw('LOWER(roles.name)'), $this->excludedFromPie)
            ->groupBy('roles.id', 'roles.name')
            ->get();

        return $roles->map(function ($role) {
            $key = strtolower(trim($role->name));
            return [
                'role'  => $this->roleLabels[$key] ?? $role->name,
                'total' => (int) $role->users_count,
            ];
        })->toArray();
    }

    // ─── Nouveaux collaborateurs par mois (6 derniers mois) ──────────────────

    public function getNouveauxParMois(): array
    {
        $mois = [];

        $moisFr = [
            1  => 'janv.', 2  => 'févr.', 3  => 'mars',
            4  => 'avr.',  5  => 'mai',   6  => 'juin',
            7  => 'juil.', 8  => 'août',  9  => 'sept.',
            10 => 'oct.',  11 => 'nov.',  12 => 'déc.',
        ];

        $now = Carbon::now()->startOfMonth();

        for ($i = 5; $i >= 0; $i--) {
            $date  = $now->clone()->subMonthsNoOverflow($i);
            $annee = (int) $date->format('Y');
            $moisN = (int) $date->format('n');

            $count = User::whereYear('date_of_hire', $annee)
                ->whereMonth('date_of_hire', $moisN)
                ->whereHas('role', function ($q) {
                    $q->whereNotIn(DB::raw('LOWER(name)'), $this->excludedFromStats);
                })
                ->count();

            $mois[] = [
                'mois'  => $moisFr[$moisN] . ' ' . $annee,
                'total' => $count,
            ];
        }

        return $mois;
    }

    // ─── Activité récente ─────────────────────────────────────────────────────

    public function getActiviteRecente(): array
    {
        $activites = [];

        $dernierCollabs = User::with('role')
            ->whereHas('role', function ($q) {
                $q->whereNotIn(DB::raw('LOWER(name)'), $this->excludedFromStats);
            })
            ->orderBy('date_of_hire', 'desc')
            ->take(8)
            ->get();

        foreach ($dernierCollabs as $user) {
            $activites[] = [
                'type'     => 'collaborateur',
                'message'  => "{$user->first_name} {$user->last_name} a rejoint l'équipe",
                'role'     => $user->role?->name,
                'date'     => Carbon::parse($user->date_of_hire)->locale('fr')->diffForHumans(),
                'date_raw' => Carbon::parse($user->date_of_hire),
            ];
        }

        usort($activites, fn($a, $b) => $b['date_raw'] <=> $a['date_raw']);

        return array_map(function ($a) {
            unset($a['date_raw']);
            return $a;
        }, array_slice($activites, 0, 8));
    }

    // ─── Alertes onboarding ───────────────────────────────────────────────────

    public function getAlertesOnboarding(): array
    {
        $alertes = [];

        //  Documents en attente de signature
        $assignments = DocumentAssignment::with(['document', 'collaborateur'])
            ->whereHas('document', function ($q) {
                $q->where('signature_req', true);
            })
            ->where('status', '!=', 'signed')
            ->take(10)
            ->get();

        foreach ($assignments as $assignment) {
            if (!$assignment->collaborateur || !$assignment->document) continue;
            $alertes[] = [
                'type'     => 'document_manquant',
                'label'    => 'Signature requise',
                'severity' => 'high',
                'user'     => $assignment->collaborateur->first_name . ' ' . $assignment->collaborateur->last_name,
                'detail'   => $assignment->document->namedoc ?? 'Document sans titre',
            ];
        }

        //  Profil incomplet (champs critiques vides)
        $collaborateurs = User::whereHas('role', function ($q) {
            $q->whereNotIn(DB::raw('LOWER(name)'), $this->excludedFromStats);
        })->get();

        foreach ($collaborateurs as $user) {
            $champsVides = [];
            if (empty($user->phone_number))   $champsVides[] = 'Téléphone';
            if (empty($user->signature_path)) $champsVides[] = 'Signature';

            if (!empty($champsVides)) {
                $alertes[] = [
                    'type'     => 'profil_incomplet',
                    'label'    => 'Profil incomplet',
                    'severity' => 'medium',
                    'user'     => $user->first_name . ' ' . $user->last_name,
                    'detail'   => implode(', ', $champsVides),
                ];
            }
        }

        //  Collaborateurs sans aucun document assigné
        $sansDocument = User::whereHas('role', function ($q) {
                $q->whereNotIn(DB::raw('LOWER(name)'), $this->excludedFromStats);
            })
            ->whereDoesntHave('assignments')
            ->take(5)
            ->get();

        foreach ($sansDocument as $user) {
            $alertes[] = [
                'type'     => 'aucun_document',
                'label'    => 'Aucun document',
                'severity' => 'low',
                'user'     => $user->first_name . ' ' . $user->last_name,
                'detail'   => 'Aucun document associé au profil',
            ];
        }

        // Trier : high → medium → low
        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($alertes, fn($a, $b) => $order[$a['severity']] <=> $order[$b['severity']]);

        return array_slice($alertes, 0, 8);
    }

    // ─── Tout en une seule requête ────────────────────────────────────────────

    public function getDashboardData(): array
    {
        return [
            'stats'              => $this->getStats(),
            'repartition_roles'  => $this->getRepartitionRoles(),
            'nouveaux_par_mois'  => $this->getNouveauxParMois(),
            'activite_recente'   => $this->getActiviteRecente(),
            'alertes_onboarding' => $this->getAlertesOnboarding(),
        ];
    }
}