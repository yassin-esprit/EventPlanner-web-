<?php

namespace App\Security;

use App\Service\LoggingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $urlGenerator;
    private $security;
    private $loggingService;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        LoggingService $loggingService
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->loggingService = $loggingService;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Get the user
        $user = $token->getUser();

        // Log the successful login
        $this->loggingService->logLoginSuccess($user);

        // Check if there's a redirect URL in the session
        if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
            return new RedirectResponse($targetPath);
        }

        // Redirect based on user role
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard_admin'));
        } elseif ($this->security->isGranted('ROLE_OWNER')) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard_owner'));
        } elseif ($this->security->isGranted('ROLE_PARTICIPANT')) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard_participant'));
        }

        // Default redirect
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }
} 