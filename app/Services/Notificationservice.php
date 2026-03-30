<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    // Créer une notification────

    public function create(int $userId, string $title, string $message, string $type = 'mail'): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'is_read' => false,
        ]);
    }

    // Notifier mail reçu

    public function notifierNouveauMail(User $destinataire, User $expediteur): Notification
    {
        return $this->create(
            $destinataire->id,
            'Nouveau message',
            "Vous avez reçu un message de {$expediteur->first_name} {$expediteur->last_name}",
            'mail'
        );
    }

    // Notifier assigné à une tâche

    public function notifierNouvelleTask(User $collaborateur, string $taskTitle): Notification
    {
        return $this->create(
            $collaborateur->id,
            'Nouvelle tâche assignée',
            "La tâche \"{$taskTitle}\" vous a été assignée",
            'task'
        );
    }

    // Récupérer les notifications de l'utilisateur connecté

    public function getMesNotifications(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'notifications' => $notifications,
            'unread_count'  => $notifications->where('is_read', false)->count(),
        ];
    }

    // Marquer une notification comme lue

    public function marquerCommeLue(int $id): Notification
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);
        return $notification;
    }

    // Marquer toutes comme lues

    public function marquerToutesCommeLues(): void
    {
        /** @var User $user */
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    // Supprimer une notification

    public function supprimer(int $id): void
    {
        /** @var User $user */
        $user = Auth::user();

        Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }
}