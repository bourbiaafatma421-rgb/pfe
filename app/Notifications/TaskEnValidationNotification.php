<?php

namespace App\Notifications;

use App\Models\OnboardingTask;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskEnValidationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly OnboardingTask $task,
        public readonly User           $collaborateur,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nom = $this->collaborateur->first_name . ' ' . $this->collaborateur->last_name;

        return (new MailMessage)
            ->subject("Tâche à valider : {$this->task->task_title}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("{$nom} a soumis la tâche « {$this->task->task_title} » pour validation.")
            ->action('Voir la tâche', url("/rh/tasks/{$this->task->id}"))
            ->line("Merci de vérifier et d'approuver ou refuser.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'task_en_validation',
            'task_id'          => $this->task->id,
            'task_title'       => $this->task->task_title,
            'collaborateur_id' => $this->collaborateur->id,
            'collaborateur'    => $this->collaborateur->first_name . ' ' . $this->collaborateur->last_name,
        ];
    }
}