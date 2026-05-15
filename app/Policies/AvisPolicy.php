<?php

namespace App\Policies;

use App\Models\Avis;
use App\Models\Onboarding;
use App\Models\User;

class AvisPolicy
{
    /**
     * Le collaborateur peut soumettre un avis
     * uniquement sur son propre onboarding
     */
    public function create(User $user, Onboarding $onboarding): bool
    {
        return $user->id === $onboarding->user_id;
    }

    /**
     * Le collaborateur peut voir uniquement son propre avis
     * Le RH et le manager peuvent voir tous les avis
     */
    public function view(User $user, Avis $avis): bool
    {
        if ($user->hasRole('rh') || $user->hasRole('manager')) {
            return true;
        }

        return $user->id === $avis->collaborateur_id;
    }
}