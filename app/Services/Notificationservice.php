<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    public function getMesNotifications(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type']       ?? null,
                'title'      => $n->data['title']      ?? null,
                'message'    => $n->data['message']    ?? null,
                'is_read'    => !is_null($n->read_at),
                'created_at' => $n->created_at,
            ]);

        return [
            'notifications' => $notifications,
            'unread_count'  => $user->unreadNotifications()->count(),
        ];
    }

    public function marquerCommeLue(string $id): array
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return [
            'id'         => $notification->id,
            'type'       => $notification->data['type']    ?? null,
            'title'      => $notification->data['title']   ?? null,
            'message'    => $notification->data['message'] ?? null,
            'is_read'    => true,
            'created_at' => $notification->created_at,
        ];
    }

    public function marquerToutesCommeLues(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
    }

    public function supprimer(string $id): void
    {
        /** @var User $user */
        $user = Auth::user();
        $user->notifications()->findOrFail($id)->delete();
    }
}