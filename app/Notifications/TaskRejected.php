<?php

namespace App\Notifications;

use App\Models\OnboardingTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskRejected extends Notification
{
    use Queueable;

    public function __construct(
        public readonly OnboardingTask $task,
        public readonly ?string        $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Tâche refusée : {$this->task->task_title}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre tâche « {$this->task->task_title} » a été refusée par votre responsable.");

        if ($this->reason) {
            $mail->line("**Motif :** {$this->reason}");
        }

        return $mail
            ->action("Voir mon plan d'intégration", url('/dashboardc/integration'))
            ->line('Merci de corriger et soumettre à nouveau.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'task_rejected',
            'task_id'    => $this->task->id,
            'task_title' => $this->task->task_title,
            'reason'     => $this->reason,
        ];
    }
}