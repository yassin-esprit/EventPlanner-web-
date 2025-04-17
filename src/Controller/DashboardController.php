<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Equipement;
use App\Repository\EvenementRepository;
use App\Repository\EquipementRepository;
use App\Repository\ReservationEquipementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository
    ): Response {
        $evenements = $evenementRepository->findAll();
        $equipements = $equipementRepository->findAll();

        return $this->render('dashboard/admin/admin.html.twig', [
            'controller_name' => 'DashboardController',
            'evenements' => $evenements,
            'equipements' => $equipements,
        ]);
    }

    #[Route('/dashboard/owner', name: 'owner_dashboard')]
    #[IsGranted('ROLE_OWNER')]
    public function ownerDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository,
        ReservationEquipementRepository $reservationEquipementRepository
    ): Response {
        $evenements = $evenementRepository->findAll();
        $equipements = $equipementRepository->findAll();
        $reservation_equipements = $reservationEquipementRepository->findAll();

        return $this->render('dashboard/owner/owner.html.twig', [
            'controller_name' => 'DashboardController',
            'evenements' => $evenements,
            'equipements' => $equipements,
            'reservation_equipements' => $reservation_equipements,
        ]);
    }

    #[Route('/dashboard/participant', name: 'participant_dashboard')]
    #[IsGranted('ROLE_PARTICIPANT')]
    public function participantDashboard(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();

        return $this->render('dashboard/participant/participant.html.twig', [
            'evenements' => $evenements,
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
