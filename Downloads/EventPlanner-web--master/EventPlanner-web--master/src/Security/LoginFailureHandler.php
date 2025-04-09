<?php

namespace App\Security;

use App\Service\LoggingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    private $urlGenerator;
    private $requestStack;
    private $loggingService;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        LoggingService $loggingService
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->loggingService = $loggingService;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Get the email from the request
        $email = $request->request->get('email');

        // Log the failed login attempt
        $this->loggingService->logLoginFailure($email, $this->getErrorMessage($exception));

        // Store the error message in the session
        $this->requestStack->getSession()->getFlashBag()->add('error', $this->getErrorMessage($exception));

        // Store the last username in the session
        $this->requestStack->getSession()->set('_security.last_username', $email);

        // Redirect back to the login page
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    private function getErrorMessage(AuthenticationException $exception): string
    {
        $message = $exception->getMessageKey();

        switch ($message) {
            case 'Invalid credentials.':
                return 'Invalid email or password.';
            case 'User account is disabled.':
                return 'Your account has been disabled. Please contact support.';
            case 'User account is locked.':
                return 'Your account has been locked due to too many failed login attempts.';
            case 'User account has expired.':
                return 'Your account has expired. Please contact support.';
            case 'Bad credentials.':
                return 'Invalid email or password.';
            default:
                return 'An error occurred during login. Please try again.';
        }
    }
} 