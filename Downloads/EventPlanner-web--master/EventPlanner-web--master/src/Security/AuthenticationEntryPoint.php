<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Store the current URL in the session for redirect after login
        if ($request->getPathInfo() !== '/login') {
            $request->getSession()->set('_security.main.target_path', $request->getUri());
        }

        // Add a flash message
        $request->getSession()->getFlashBag()->add('warning', 'Please log in to access this page.');

        // Redirect to the login page
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
} 