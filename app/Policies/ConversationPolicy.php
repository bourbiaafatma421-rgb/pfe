<?php
namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function access(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->rh_id === (int) $user->id
            || (int) $conversation->collaborateur_id === (int) $user->id;
    }
}