<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\PubliciteRepository;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Evenement;
use App\Entity\Publicite;


class DashboardController extends AbstractController
{
    #[Route('/dashboard/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(EvenementRepository $evenementRepository, PubliciteRepository $publiciteRepository): Response
    {
                 $evenements = $evenementRepository->findAll();
                 $publicites = $publiciteRepository->findAll();


        return $this->render('dashboard/admin/admin.html.twig', [
            'controller_name' => 'DashboardController',
            'evenements' => $evenements,
            'publicites' => $publicites,

        ]);
    }

    #[Route('/dashboard/owner', name: 'owner_dashboard')]
    #[IsGranted('ROLE_OWNER')]
    public function ownerDashboard(EvenementRepository $evenementRepository,PubliciteRepository $publiciteRepository ): Response
    {
         $evenements = $evenementRepository->findAll();
         $publicites = $publiciteRepository->findAll();
        return $this->render('dashboard/owner/owner.html.twig', [
            'controller_name' => 'DashboardController',
            'evenements' => $evenements, 
            'publicites' => $publicites,
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