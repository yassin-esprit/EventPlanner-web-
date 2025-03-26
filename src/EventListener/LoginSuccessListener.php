<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $route = 'admin_dashboard';
        } elseif (in_array('ROLE_OWNER', $roles)) {
            $route = 'owner_dashboard';
        } else {
            $route = 'participant_dashboard';
        }

        $response = new RedirectResponse($this->urlGenerator->generate($route));
        $event->setResponse($response);
    }
}