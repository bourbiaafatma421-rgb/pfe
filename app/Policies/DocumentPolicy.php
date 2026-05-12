<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function view(User $user, Document $document): bool
    {
        if (strtolower($user->role->name) === 'rh') {
            return true;
        }

        return $document->assignments->contains('user_id', $user->id);
    }

    public function viewOwn(User $user): bool
    {
        return strtolower($user->role->name) === 'collaborateur';
    }

    public function create(User $user): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function update(User $user, Document $document): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function delete(User $user, ?Document $document = null): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function restore(User $user, Document $document): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return strtolower($user->role->name) === 'rh';
    }

    public function sign(User $user, Document $document): bool
    {
        return $document->assignments->contains('user_id', $user->id);
    }
}