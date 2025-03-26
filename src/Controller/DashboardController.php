<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Entity\Evenement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(): Response
    {
        return $this->render('dashboard/admin.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/owner/dashboard', name: 'owner_dashboard')]
    #[IsGranted('ROLE_OWNER')]
    public function ownerDashboard(): Response
    {
        return $this->render('dashboard/owner.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/dashboard', name: 'participant_dashboard')]
    #[IsGranted('ROLE_PARTICIPANT')]
    public function participantDashboard(EvenementRepository $evenementRepository): Response
    {
        // Get all events (or filter as needed)
        $evenements = $evenementRepository->findAll();
        
        // Alternatively, you could filter for upcoming events only:
        // $evenements = $evenementRepository->findUpcomingEvents();
        
        return $this->render('dashboard/participant.html.twig', [
            'evenements' => $evenements,
        ]);
    }


    #[Route('/participant/event/{id}', name: 'app_evenement_participant_show', methods: ['GET'])]
public function showParticipant(Evenement $evenement): Response
{
    return $this->render('evenement/showEVparticipant.html.twig', [
        'evenement' => $evenement,
    ]);
}
}