<?php

namespace App\Controller;

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
    public function participantDashboard(): Response
    {
        return $this->render('dashboard/participant.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}