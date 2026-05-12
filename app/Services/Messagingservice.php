<?php
namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MessagingService
{
    public function getConversationsFor(User $user): Collection
    {
        $field = $user->isRh() ? 'rh_id' : 'collaborateur_id';

        return Conversation::with(['rh', 'collaborateur', 'lastMessage'])
            ->where($field, $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn(Conversation $c) => [
                'id'           => $c->id,
                'other'        => $c->otherParticipant($user->id)->only(['id', 'first_name', 'last_name', 'avatar_path']),
                'last_message' => $c->lastMessage?->only(['body', 'created_at', 'sender_id']),
                'unread_count' => $c->unreadCountFor($user->id),
            ]);
    }

    public function getOrCreateConversation(int $rhId, int $collaborateurId): Conversation
    {
        return Conversation::findOrCreateBetween($rhId, $collaborateurId);
    }

    public function getMessages(Conversation $conversation, User $reader, int $perPage = 30): LengthAwarePaginator
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $conversation->messages()
            ->with('sender:id,first_name,last_name,avatar_path')
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    public function sendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body'      => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $recipient = $conversation->otherParticipant($sender->id);
        $recipient->notify(new MessageReceivedNotification($message, $sender));

        return $message;
    }

    public function totalUnreadFor(User $user): int
    {
        $field = $user->isRh() ? 'rh_id' : 'collaborateur_id';

        $conversationIds = Conversation::where($field, $user->id)->pluck('id');

        return Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function userBelongsToConversation(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->rh_id === (int) $user->id
            || (int) $conversation->collaborateur_id === (int) $user->id;
    }
    public function searchCollaborateurs(string $search): Collection
{
    return User::whereHas('role', fn($q) => $q->where('name', 'new_collaborateur'))
        ->where('active', true)
        ->where(function ($q) use ($search) {
            $q->where('first_name', 'ilike', "%{$search}%")
              ->orWhere('last_name',  'ilike', "%{$search}%")
              ->orWhere('email',      'ilike', "%{$search}%");
        })
        ->select('id', 'first_name', 'last_name', 'email')
        ->orderBy('last_name')
        ->limit(20)
        ->get();
}
}