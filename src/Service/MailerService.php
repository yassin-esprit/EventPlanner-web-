<?php

// src/Service/MailerService.php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendToSponsor(string $to, string $sponsorName, string $contratDetails): void
    {
        $email = (new Email())
            ->from('medrachidtalmoudi@gmail.com')
            ->to($to)
            ->subject('Nouveau contrat ajouté')
            ->text("Bonjour $sponsorName,\n\nUn nouveau contrat vous a été assigné :\n$contratDetails")
            ->html("
                <p>Bonjour <strong>$sponsorName</strong>,</p>
                <p>Un nouveau contrat vous a été assigné :</p>
                <p><em>$contratDetails</em></p>
                <p>Cordialement,<br>Votre équipe</p>
            ");

        // Envoi direct de l'email sans passer par la base de données ou Messenger
        $this->mailer->send($email);
    }
}
