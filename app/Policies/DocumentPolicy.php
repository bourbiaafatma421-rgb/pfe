<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'rh';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        if (strtolower($user->role->name) === 'rh') {
            return true;
        }

        return $document->assignments->contains('user_id', $user->id);
    }

    public function viewOwn(User $user): bool
    {
        return strtolower($user->role->name) === 'new_collaborateur';
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    return strtolower($user->role->name) === 'rh';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->role->name === 'rh';
    }

    /**
     * Determine whether the user can delete the model.
     */
  public function delete(User $user, ?Document $document = null): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->role->name === 'rh';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $user->role->name === 'rh';
    }

}
