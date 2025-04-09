<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_ADMIN')] // Ensure only admins can access routes in this controller
class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard_admin')] // Changed route name to dashboard_admin
    public function index(): Response
    {
        // Replace with your actual dashboard logic and template rendering
        return $this->render('admin/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
} 