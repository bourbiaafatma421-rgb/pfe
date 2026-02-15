<?php

namespace App\Policies;

//use App\Models\Staff;
use App\Models\User;
//use Illuminate\Auth\Access\Response;

class StaffPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('MANAGER');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->hasRole('MANAGER');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('MANAGER');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->hasRole('MANAGER');          

    }

    
    public function restore(User $user): bool
    {
        return false;
    }

    
}
