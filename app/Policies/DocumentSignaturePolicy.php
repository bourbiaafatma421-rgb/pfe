<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\DocumentAssignment;
use App\Models\DocumentSignature;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentSignaturePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['RH', 'MANAGER']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentSignature $documentSignature): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentSignature $documentSignature): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentSignature $documentSignature): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentSignature $documentSignature): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentSignature $documentSignature): bool
    {
        return false;
    }

    public function sign(User $user, Document $document): Response
    {
        if ($user->role->name !== 'new_collaborateur') {
            return Response::deny('Vous n\'avez pas le droit de signer ce document.');
        }

        $isAssigned = DocumentAssignment::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        return $isAssigned
            ? Response::allow()
            : Response::deny('Vous n\'êtes pas assigné à ce document.');
    }

}
