<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Equipement;
use App\Repository\EvenementRepository;
use App\Repository\EquipementRepository;
use App\Repository\ReservationEquipementRepository;
use App\Repository\PubliciteRepository;
use App\Repository\CampagnesPublicitaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Service\BadgeService;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository,
        PubliciteRepository $publiciteRepository,
        CampagnesPublicitaireRepository $campagneRepository
    ): Response {
        $evenements   = $evenementRepository->findAll();
        $equipements  = $equipementRepository->findAll();
        $publicites   = $publiciteRepository->findAll();
        $campagnes    = $campagneRepository->findAll();

        return $this->render('dashboard/admin/admin.html.twig', [
            'controller_name' => 'DashboardController',
            'evenements'      => $evenements,
            'equipements'     => $equipements,
            'publicites'      => $publicites,
            'campagnes'       => $campagnes,
        ]);
    }

    #[Route('/dashboard/owner', name: 'owner_dashboard')]
    #[IsGranted('ROLE_OWNER')]
    public function ownerDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository,
        ReservationEquipementRepository $reservationEquipementRepository,
        PubliciteRepository $publiciteRepository,
        CampagnesPublicitaireRepository $campagneRepository
    ): Response {
        $evenements            = $evenementRepository->findAll();
        $equipements           = $equipementRepository->findAll();
        $reservationEquipements = $reservationEquipementRepository->findAll();
        $publicites            = $publiciteRepository->findAll();
        $campagnes             = $campagneRepository->findAll();

        return $this->render('dashboard/owner/owner.html.twig', [
            'controller_name'        => 'DashboardController',
            'evenements'             => $evenements,
            'equipements'            => $equipements,
            'reservation_equipements'=> $reservationEquipements,
            'publicites'             => $publicites,
            'campagnes'              => $campagnes,
        ]);
    }

    #[Route('/dashboard/participant', name: 'participant_dashboard')]
    #[IsGranted('ROLE_PARTICIPANT')]
    public function participantDashboard(
        EvenementRepository $evenementRepository,
        Request $request,
        BadgeService $badgeService
    ): Response {
        $evenements = $evenementRepository->findAll();

        // Example of handling search and sort functionality
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'name'); // Default sort field
        $direction = $request->query->get('direction', 'asc'); // Default sort direction

        // Pass the badge service to the view
        return $this->render('dashboard/participant/participant.html.twig', [
            'evenements' => $evenements,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'badge_service' => $badgeService,
        ]);
    }

    #[Route('/dashboard/participant/event/{id}', name: 'app_evenement_participant_show', methods: ['GET'])]
    public function showParticipant(Evenement $evenement): Response
    {
        return $this->render('evenement/showEVparticipant.html.twig', [
            'evenement' => $evenement,
        ]);
    }
}
