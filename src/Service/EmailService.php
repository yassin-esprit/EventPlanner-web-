<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailService
{
    private $mailer;
    private $fromEmail;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->fromEmail = 'medrachidtalmoudi@gmail.com';
        $this->logger = $logger;
    }

    public function sendContratNotification(string $toEmail, string $sponsorName, array $contratDetails): bool
    {
        try {
            $this->logger->debug('Début de la tentative d\'envoi d\'email de notification de contrat', [
                'to' => $toEmail,
                'sponsor' => $sponsorName,
                'contratDetails' => $contratDetails
            ]);

            $email = (new Email())
                ->from(new Address($this->fromEmail, 'Event Planner System'))
                ->to($toEmail)
                ->subject('Nouveau Contrat - Notification')
                ->html($this->createEmailBody($sponsorName, $contratDetails));

            $this->mailer->send($email);
            
            $this->logger->info('Email envoyé avec succès', [
                'to' => $toEmail,
                'sponsor' => $sponsorName
            ]);
            
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email', [
                'error' => $e->getMessage(),
                'to' => $toEmail,
                'sponsor' => $sponsorName
            ]);
            
            return false;
        }
    }

    private function createEmailBody(string $sponsorName, array $contratDetails): string
    {
        return "
            <h2>Bonjour {$sponsorName},</h2>
            <p>Un nouveau contrat a été créé dans notre système. Voici les détails :</p>
            <ul>
                <li><strong>Type de contrat :</strong> {$contratDetails['type_contrat']}</li>
                <li><strong>Montant :</strong> {$contratDetails['montant']} TND</li>
                <li><strong>Description :</strong> {$contratDetails['description']}</li>
                <li><strong>Date de début :</strong> {$contratDetails['date_debut']->format('d/m/Y')}</li>
                <li><strong>Date de fin :</strong> {$contratDetails['date_fin']->format('d/m/Y')}</li>
                <li><strong>Statut :</strong> {$contratDetails['statut']}</li>
                <li><strong>Mode de paiement :</strong> {$contratDetails['mode_paiement']}</li>
            </ul>
            <p>Merci de votre confiance.</p>
            <p>Cordialement,<br>L'équipe Event Planner</p>
        ";
    }
} 