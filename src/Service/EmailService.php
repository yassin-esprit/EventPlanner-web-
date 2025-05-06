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
        // Générer l'URL d'impression
        $printUrl = "http://localhost:8000/contrat/{$contratDetails['id_contrat']}/print";

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Poppins', sans-serif;
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    padding: 20px;
                    color: #2d3436;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 20px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                    overflow: hidden;
                    animation: slideUp 0.5s ease-out;
                }
                
                .email-header {
                    background: linear-gradient(135deg, #6c5ce7 0%, #a363d5 100%);
                    padding: 40px 30px;
                    text-align: center;
                    color: white;
                }
                
                .email-header h1 {
                    font-size: 28px;
                    font-weight: 600;
                    margin-bottom: 10px;
                    animation: fadeIn 1s ease-out;
                }
                
                .email-content {
                    padding: 40px 30px;
                }
                
                .greeting {
                    font-size: 24px;
                    color: #2d3436;
                    margin-bottom: 20px;
                    font-weight: 500;
                }
                
                .info-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    margin: 30px 0;
                }
                
                .info-table tr {
                    transition: transform 0.3s ease;
                }
                
                .info-table tr:hover {
                    transform: translateX(10px);
                    background-color: #f8f9fa;
                }
                
                .info-table td {
                    padding: 15px;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .info-table td:first-child {
                    font-weight: 600;
                    color: #6c5ce7;
                    width: 40%;
                }
                
                .highlight {
                    background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
                    padding: 3px 8px;
                    border-radius: 4px;
                    color: white;
                    font-weight: 500;
                }
                
                .print-button {
                    display: inline-block;
                    margin: 30px auto;
                    padding: 15px 30px;
                    background: linear-gradient(135deg, #6c5ce7 0%, #a363d5 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 500;
                    text-align: center;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(108, 92, 231, 0.2);
                    position: relative;
                    overflow: hidden;
                }
                
                .print-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(108, 92, 231, 0.3);
                }
                
                .print-button:active {
                    transform: translateY(-1px);
                }
                
                .print-button::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(
                        120deg,
                        transparent,
                        rgba(255, 255, 255, 0.2),
                        transparent
                    );
                    transition: 0.5s;
                }
                
                .print-button:hover::before {
                    left: 100%;
                }
                
                .print-icon {
                    margin-right: 8px;
                    vertical-align: middle;
                }
                
                .footer {
                    background: #f8f9fa;
                    padding: 30px;
                    text-align: center;
                    border-top: 1px solid #e9ecef;
                }
                
                .social-links {
                    margin: 20px 0;
                }
                
                .social-links a {
                    display: inline-block;
                    margin: 0 10px;
                    color: #6c5ce7;
                    text-decoration: none;
                    transition: transform 0.3s ease;
                }
                
                .social-links a:hover {
                    transform: translateY(-3px);
                }
                
                .copyright {
                    font-size: 12px;
                    color: #6c757d;
                }
                
                @keyframes slideUp {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }
                    to {
                        opacity: 1;
                    }
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        margin: 10px;
                    }
                    
                    .email-header {
                        padding: 30px 20px;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .greeting {
                        font-size: 20px;
                    }
                    
                    .print-button {
                        padding: 12px 24px;
                        font-size: 14px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>Nouveau Contrat Créé</h1>
                    <p>Event Planner System</p>
                </div>
                
                <div class='email-content'>
                    <div class='greeting'>Bonjour {$sponsorName},</div>
                    
                    <p style='line-height: 1.6; color: #636e72;'>
                        Nous sommes ravis de vous informer qu'un <span class='highlight'>nouveau contrat</span> a été créé dans notre système. 
                        Voici un résumé des informations importantes :
                    </p>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Type de contrat</td>
                            <td>{$contratDetails['type_contrat']}</td>
                        </tr>
                        <tr>
                            <td>Montant</td>
                            <td><strong>{$contratDetails['montant']} TND</strong></td>
                        </tr>
                        <tr>
                            <td>Description</td>
                            <td>{$contratDetails['description']}</td>
                        </tr>
                        <tr>
                            <td>Date de début</td>
                            <td>{$contratDetails['date_debut']->format('d/m/Y')}</td>
                        </tr>
                        <tr>
                            <td>Date de fin</td>
                            <td>{$contratDetails['date_fin']->format('d/m/Y')}</td>
                        </tr>
                        <tr>
                            <td>Statut</td>
                            <td><span class='highlight'>{$contratDetails['statut']}</span></td>
                        </tr>
                        <tr>
                            <td>Mode de paiement</td>
                            <td>{$contratDetails['mode_paiement']}</td>
                        </tr>
                    </table>
                    
                    <div style='text-align: center;'>
                        <a href='{$printUrl}' class='print-button' target='_blank'>
                            <svg class='print-icon' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                <polyline points='6 9 6 2 18 2 18 9'></polyline>
                                <path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'></path>
                                <rect x='6' y='14' width='12' height='8'></rect>
                            </svg>
                            Voir le Contrat en PDF
                        </a>
                    </div>
                    
                    <p style='margin-top: 30px; line-height: 1.6; color: #636e72;'>
                        Merci de votre confiance. Notre équipe reste à votre disposition pour toute question.
                    </p>
                    
                    <p style='margin-top: 20px; line-height: 1.6; color: #636e72;'>
                        Cordialement,<br>
                        <strong style='color: #2d3436;'>L'équipe Event Planner</strong>
                    </p>
                </div>
                
                <div class='footer'>
                    <p class='copyright'>© " . date('Y') . " Event Planner. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}