<?php

namespace App\Controller;

use App\Entity\ReservationEquipement;
use App\Form\ReservationEquipementType;
use App\Repository\ReservationEquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Psr\Log\LoggerInterface; 

use App\Service\GoogleCalendarService;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Calendar;
use App\Entity\Equipement;
#[Route('/reservation/equipement')]
final class ReservationEquipementController extends AbstractController{



    #[Route('/reservation/equipement/{userId}', name: 'app_reservation_equipement_index', methods: ['GET'])]
public function index(int $userId, ReservationEquipementRepository $reservationEquipementRepository): Response
{
    $reservationEquipements = $reservationEquipementRepository->findByUser($userId);

    return $this->render('reservation_equipement/index.html.twig', [
        'reservation_equipements' => $reservationEquipements,
        'userId' => $userId
    ]);
}




  


#[Route('/new', name: 'app_reservation_equipement_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $reservationEquipement = new ReservationEquipement();

    // Set the current user
    if ($this->getUser()) {
        $reservationEquipement->setUser($this->getUser());
    } else {
        throw $this->createAccessDeniedException('You must be logged in to add an event.');
    }
    $form = $this->createForm(ReservationEquipementType::class, $reservationEquipement);
    $form->handleRequest($request);

    // Debug temporaire pour confirmer la soumission
    if ($form->isSubmitted()) {
        dump('Soumis: ✅');
        dump('Valide: ', $form->isValid());

        if ($form->isValid()) {
            try {
                $entityManager->persist($reservationEquipement);
                $entityManager->flush();

                $this->addFlash('success', 'Réservation créée avec succès');
                return $this->redirectToRoute('app_reservation_equipement_index', ['userId' => $this->getUser()->getUserId()]);
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', 'L\'équipement ou l\'événement sélectionné n\'existe pas ou n\'est plus disponible');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de la réservation');
            }
        }
    }

    return $this->render('reservation_equipement/new.html.twig', [
        'form' => $form->createView(),
        'userId' => $this->getUser()->getUserId(), // Pass userId to the template
    ]);
}
    
     #[Route( name:'app_reservation_search', methods: ['GET'])]
 
public function searchReservations(Request $request): Response
{
    $searchTerm = $request->query->get('q');
    $status = $request->query->get('statut');

    $queryBuilder = $this->getDoctrine()
        ->getRepository(ReservationEquipement::class)
        ->createQueryBuilder('r')
        ->leftJoin('r.equipement', 'e')
        ->leftJoin('r.evenement', 'ev');

    if ($searchTerm) {
        $queryBuilder->andWhere('e.nom LIKE :term OR ev.titre LIKE :term')
            ->setParameter('term', '%'.$searchTerm.'%');
    }

    if ($status && $status !== 'all') {
        $queryBuilder->andWhere('r.statut = :status')
            ->setParameter('status', $status);
    }

    $reservations = $queryBuilder->getQuery()->getResult();

    return $this->render('dashboard/admin/admin.html.twig', [
        'reservations' => $reservations
    ]);
}

#[Route('/show/{id}', name: 'app_reservation_equipement_show', methods: ['GET'])]
public function show(int $id, ReservationEquipementRepository $reservationEquipementRepository): Response
    {
        $reservationEquipement = $reservationEquipementRepository->find($id);

        if (!$reservationEquipement) {
            $this->addFlash('error', 'Reservation not found');
            return $this->redirectToRoute('app_reservation_equipement_index', [
                'userId' => $this->getUser() ? $this->getUser()->getUserId() : null,
            ]);
        }

        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('You must be logged in to view this reservation.');
        }

        return $this->render('reservation_equipement/show.html.twig', [
            'reservation_equipement' => $reservationEquipement,
            'userId' => $this->getUser()->getUserId(),
        ]);
    }



    #[Route('/{id}/edit', name: 'app_reservation_equipement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationEquipement $reservationEquipement, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('You must be logged in to edit this reservation.');
        }

        $form = $this->createForm(ReservationEquipementType::class, $reservationEquipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Réservation modifiée avec succès');
            return $this->redirectToRoute('app_reservation_equipement_index', ['userId' => $this->getUser()->getUserId()]);
        }

        return $this->render('reservation_equipement/edit.html.twig', [
            'reservation_equipement' => $reservationEquipement,
            'form' => $form->createView(),
            'userId' => $this->getUser()->getUserId(),
        ]);
    }

    
    #[Route('/new/from-equipement/{id}', name: 'app_reservation_equipement_new_from_equipement', methods: ['GET', 'POST'])]

    public function newFromEquipement(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipement = $entityManager->getRepository(Equipement::class)->find($id);

        if (!$equipement) {
            throw $this->createNotFoundException('Équipement introuvable');
        }

        $reservation = new ReservationEquipement();
        $reservation->setEquipement($equipement);

        if ($this->getUser()) {
            $reservation->setUser($this->getUser());
        } else {
            throw $this->createAccessDeniedException('You must be logged in to add an event.');
        }

        $form = $this->createForm(ReservationEquipementType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation créée avec succès');
            return $this->redirectToRoute('app_reservation_equipement_index', ['userId' => $this->getUser()->getUserId()]);
        }

        return $this->render('reservation_equipement/new.html.twig', [
            'form' => $form->createView(),
            'userId' => $this->getUser()->getUserId(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_reservation_equipement_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, ReservationEquipementRepository $reservationEquipementRepository, EntityManagerInterface $entityManager): Response
    {
        $reservationEquipement = $reservationEquipementRepository->find($id);

        if (!$reservationEquipement) {
            $this->addFlash('error', 'Reservation not found');
            return $this->redirectToRoute('app_reservation_equipement_index', [
                'userId' => $this->getUser() ? $this->getUser()->getUserId() : null,
            ]);
        }

        if ($this->isCsrfTokenValid('delete' . $reservationEquipement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationEquipement);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès');
        }

        return $this->redirectToRoute('app_reservation_equipement_index', [
            'userId' => $this->getUser() ? $this->getUser()->getUserId() : null,
        ]);
    }


    #[Route('/Reservation/{id}/add-calendar', name: 'Reservation_add_calendar')]
    public function addToCalendar(
        int $id, 
        ReservationEquipementRepository $reservationRepository,
        GoogleCalendarService $googleCalendarService,
        Request $request,
        LoggerInterface $logger
    ): Response {
        // Ensure user is logged in
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('You must be logged in to add to calendar.');
        }
    
        // Store the reservation ID in session so we can retrieve it after auth redirect
        $request->getSession()->set('pending_calendar_reservation_id', $id);
    
        $reservationEquipement = $reservationRepository->find($id);
        
        if (!$reservationEquipement) {
            throw $this->createNotFoundException('Equipment reservation not found');
        }
        
        try {
            $client = $googleCalendarService->getClient();
            
            // Check if we have a valid access token
            if (!$request->getSession()->has('google_access_token')) {
                // If no token, generate auth URL and redirect user
                $authUrl = $client->createAuthUrl();
                return $this->redirect($authUrl);
            }
            
            // Set the access token
            $accessToken = $request->getSession()->get('google_access_token');
            $client->setAccessToken($accessToken);
            
            // If token has expired, refresh it
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $request->getSession()->set('google_access_token', $client->getAccessToken());
                } else {
                    // No refresh token, need to re-authenticate
                    $request->getSession()->remove('google_access_token');
                    $authUrl = $client->createAuthUrl();
                    return $this->redirect($authUrl);
                }
            }
            
            // Create Calendar service
            $service = new \Google\Service\Calendar($client);
            
            // Make sure $service is properly initialized
            if (!$service || !$service->events) {
                throw new \Exception('Google Calendar service not properly initialized');
            }
            
            // Use your service to create the event - Fixed the variable name
            $event = $googleCalendarService->createEventFromEntity($reservationEquipement);
            $createdEvent = $service->events->insert('primary', $event);
            
            $this->addFlash('success', 'Event added to Google Calendar');
            
            // Redirect to the Google Calendar event URL directly
            return $this->redirect($createdEvent->getHtmlLink());
        
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to add to calendar: ' . $e->getMessage());
            $logger->error('Google Calendar error', ['exception' => $e]);
    
            return $this->redirectToRoute('app_reservation_equipement_index', [
                'userId' => $this->getUser() ? $this->getUser()->getUserId() : null,
            ]);
        }
    }
    
    #[Route('/google-calendar-auth', name: 'google_calendar_auth')]
    public function handleGoogleAuth(
        Request $request, 
        GoogleCalendarService $googleCalendarService,
        ReservationEquipementRepository $reservationRepository
    ): Response {
        $client = $googleCalendarService->getClient();
    
        if ($request->query->has('code')) {
            try {
                $client->fetchAccessTokenWithAuthCode($request->query->get('code'));
                $request->getSession()->set('google_access_token', $client->getAccessToken());
                
                // Check if we have a pending reservation to add to calendar
                if ($request->getSession()->has('pending_calendar_reservation_id')) {
                    $reservationId = $request->getSession()->get('pending_calendar_reservation_id');
                    $request->getSession()->remove('pending_calendar_reservation_id');
                    
                    // Redirect back to add calendar action to complete the process
                    return $this->redirectToRoute('Reservation_add_calendar', ['id' => $reservationId]);
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Google authentication failed: ' . $e->getMessage());
            }
        }
        
        // Default fallback redirect
        return $this->redirectToRoute('app_reservation_equipement_index', [
            'userId' => $this->getUser() ? $this->getUser()->getUserId() : null,
        ]);
    }
}