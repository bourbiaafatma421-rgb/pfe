<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaborateurBienvenueMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $prenom;
    public string $nom;
    public string $email;
    public string $motDePasse;
    public string $role;
    public string $signatureUrl;

    public function __construct(
        string $prenom,
        string $nom,
        string $email,
        string $motDePasse,
        string $role,
        string $signatureUrl
    ) {
        $this->prenom       = $prenom;
        $this->nom          = $nom;
        $this->email        = $email;
        $this->motDePasse   = $motDePasse;
        $this->role         = $role;
        $this->signatureUrl = $signatureUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue chez Maison du Web — Vos accès',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborateur-bienvenue',
        );
    }
}