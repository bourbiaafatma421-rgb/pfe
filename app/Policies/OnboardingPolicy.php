<?php

namespace App\Policies;

use App\Models\Onboarding;
use App\Models\User;

class OnboardingPolicy
{
    // ── Voir la liste de tous les onboardings ─────────────
    public function viewAny(User $user): bool
    {
        return $user->hasRole('rh') || $user->hasRole('manager');
    }

    // ── Voir un onboarding spécifique ─────────────────────
    public function view(User $user, Onboarding $onboarding): bool
    {
        // RH et manager voient tout
        if ($user->hasRole('rh') || $user->hasRole('manager')) {
            return true;
        }

        // Collaborateur voit uniquement le sien
        return $user->id === $onboarding->user_id;
    }

    // ── Générer un plan IA (RH uniquement) ────────────────
    public function generate(User $user): bool
    {
        return $user->hasRole('rh');
    }

    // ── Valider un onboarding (RH uniquement) ─────────────
    public function valider(User $user, Onboarding $onboarding): bool
    {
        if (!$user->hasRole('rh')) {
            return false;
        }

        // Ne peut pas valider ce qui est déjà validé
        return !$onboarding->isValide();
    }

    // ── Modifier une tâche (RH uniquement) ────────────────
    public function updateTask(User $user): bool
    {
        return $user->hasRole('rh');
    }

    // ── Ajouter une tâche (RH uniquement) ─────────────────
    public function addTask(User $user): bool
    {
        return $user->hasRole('rh');
    }

    // ── Supprimer une tâche (RH uniquement) ───────────────
    public function deleteTask(User $user): bool
    {
        return $user->hasRole('rh');
    }
}