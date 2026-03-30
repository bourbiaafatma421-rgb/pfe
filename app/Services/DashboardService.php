<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardService
{
    // ─── Stats principales ────────────────────────────────────────────────────

    public function getStats(): array
    {
        // Total collaborateurs (hors manager)
        $managerRole = Role::whereRaw('LOWER(name) = ?', ['manager'])->first();
        $query = User::query();
        if ($managerRole) {
            $query->where('role_id', '!=', $managerRole->id);
        }

        $totalCollaborateurs = $query->count();
        $collaborateursActifs = (clone $query)->where('active', true)->count();
        $collaborateursInactifs = (clone $query)->where('active', false)->count();

        // Documents en attente de signature
        //$documentsEnAttente = Document::where('signature_req', true)
            //->whereDoesntHave('signatures', function ($q) {
             //   $q->where('status', 'signed');
           // })
            //->count();

        return [
            'total_collaborateurs'    => $totalCollaborateurs,
            'collaborateurs_actifs'   => $collaborateursActifs,
            'collaborateurs_inactifs' => $collaborateursInactifs,
            //'documents_en_attente'    => $documentsEnAttente,
        ];
    }

    // ─── Répartition par rôle ─────────────────────────────────────────────────

    public function getRepartitionRoles(): array
    {
        $roles = Role::withCount('users')
            ->whereRaw('LOWER(name) != ?', ['manager'])
            ->get();

        return $roles->map(function ($role) {
            return [
                'role'  => $role->name,
                'total' => $role->users_count,
            ];
        })->toArray();
    }

    // ─── Nouveaux collaborateurs par mois (6 derniers mois) ──────────────────

    public function getNouveauxParMois(): array
    {
        $mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereHas('role', fn($q) => $q->whereRaw('LOWER(name) != ?', ['manager']))
                ->count();

            $mois[] = [
                'mois'  => $date->translatedFormat('M Y'),
                'total' => $count,
            ];
        }
        return $mois;
    }

    // ─── Activité récente ─────────────────────────────────────────────────────

    public function getActiviteRecente(): array
    {
        $activites = [];

        // Derniers collaborateurs créés
        $dernierCollabs = User::with('role')
            ->whereHas('role', fn($q) => $q->whereRaw('LOWER(name) != ?', ['manager']))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($dernierCollabs as $user) {
            $activites[] = [
                'type'    => 'collaborateur',
                'message' => "{$user->first_name} {$user->last_name} a rejoint l'équipe",
                'role'    => $user->role?->name,
                'date'    => $user->created_at->diffForHumans(),
                'date_raw'=> $user->created_at,
            ];
        }

        // Derniers documents ajoutés
        //$derniersDocs = Document::orderBy('created_at', 'desc')->take(3)->get();
        //foreach ($derniersDocs as $doc) {
        //    $activites[] = [
        //        'type'    => 'document',
        //        'message' => "Document \"{$doc->namedoc}\" ajouté",
        //        'role'    => null,
         //       'date'    => $doc->created_at->diffForHumans(),
         //       'date_raw'=> $doc->created_at,
         //   ];
       // }

        // Trier par date
        usort($activites, fn($a, $b) => $b['date_raw'] <=> $a['date_raw']);

        // Supprimer date_raw avant retour
        return array_map(function ($a) {
            unset($a['date_raw']);
            return $a;
        }, array_slice($activites, 0, 8));
    }

    // ─── Tout en une seule requête ────────────────────────────────────────────

    public function getDashboardData(): array
    {
        return [
            'stats'            => $this->getStats(),
            'repartition_roles'=> $this->getRepartitionRoles(),
            'nouveaux_par_mois'=> $this->getNouveauxParMois(),
            'activite_recente' => $this->getActiviteRecente(),
        ];
    }
}