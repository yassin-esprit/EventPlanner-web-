<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityRedirectController extends AbstractController
{
    #[Route('/dashboard/redirect', name: 'app_dashboard_redirect')]
    public function redirectAfterLogin(): RedirectResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        if ($this->isGranted('ROLE_OWNER')) {
            return $this->redirectToRoute('owner_dashboard');
        }
        if ($this->isGranted('ROLE_PARTICIPANT')) {
            return $this->redirectToRoute('participant_dashboard');
        }
        
        return $this->redirectToRoute('app_home');
    }
}