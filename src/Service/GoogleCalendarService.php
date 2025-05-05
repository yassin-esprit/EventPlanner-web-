<?php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class GoogleCalendarService
{
    private $params;
    private $router;
    private $security;

    public function __construct(ParameterBagInterface $params, UrlGeneratorInterface $router, Security $security)
    {
        $this->params = $params;
        $this->router = $router;
        $this->security = $security;
    }

    public function getClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Symfony Calendar');
        $client->setScopes([Calendar::CALENDAR_EVENTS]);
        $client->setAuthConfig($this->params->get('google_calendar_credentials_path'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Set redirect URI for web application
        $client->setRedirectUri(
            $this->router->generate('google_calendar_auth', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        return $client;
    }

    public function createEventFromEntity($reservationEquipement): Event
    {
        // Check if it's a ReservationEquipement entity
        if ($reservationEquipement instanceof \App\Entity\ReservationEquipement) {
            // Use the reservation date as the start date
            $startDateTime = clone $reservationEquipement->getDateReservation();
            $startDateTime->setTime(0, 0); // Set to start of day or use a specific time if available
            
            $endDateTime = clone $startDateTime;
            $endDateTime->modify('+1 day'); // Or adjust based on your reservation duration
            
            $equipement = $reservationEquipement->getEquipement();
            $user = $reservationEquipement->getUser();
            
            $event = new Event([
                'summary' => 'Equipment Reservation: ' . $equipement->getNom(),
                'description' => 'Reserved by: ' . $user->getFirstName() . "\n" . 
                               'Equipment: ' . $equipement->getDescription() ?? 'No description',
                'start' => new EventDateTime([
                    'dateTime' => $startDateTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => 'Africa/Tunis',
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $endDateTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => 'Africa/Tunis',
                ]),
            ]);
            
            return $event;
        }
        
        throw new \InvalidArgumentException('Expected ReservationEquipement entity');
    }

    public function syncEvents(array $events): string
    {
        $client = $this->getClient();
        $service = new Calendar($client);
        
        $lastEventUrl = '';
        
        foreach ($events as $event) {
            $googleEvent = $this->createEventFromEntity($event);
            $createdEvent = $service->events->insert('primary', $googleEvent);
            $lastEventUrl = $createdEvent->getHtmlLink();
        }
        
        return $lastEventUrl;
    }
}