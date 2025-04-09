<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class LoggingService
{
    private $entityManager;
    private $requestStack;
    private $security;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;

        // Initialize Monolog logger
        $this->logger = new Logger('app');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/app.log', Logger::DEBUG));
    }

    public function log(string $action, string $description, string $level = 'INFO', ?array $context = null): void
    {
        // Get current user
        $user = $this->security->getUser();
        $userId = $user instanceof User ? $user->getUserId() : null;

        // Get IP address
        $ipAddress = $this->requestStack->getCurrentRequest()?->getClientIp();

        // Create database log
        $log = new Log();
        $log->setAction($action);
        $log->setDescription($description);
        $log->setLevel($level);
        $log->setIpAddress($ipAddress);
        $log->setContext($context);

        if ($userId) {
            $log->setUser($user);
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        // Log to file
        $message = sprintf(
            '[%s] %s - %s - User: %s - IP: %s',
            $action,
            $description,
            $level,
            $userId ?? 'anonymous',
            $ipAddress ?? 'unknown'
        );

        switch ($level) {
            case 'DEBUG':
                $this->logger->debug($message, $context ?? []);
                break;
            case 'INFO':
                $this->logger->info($message, $context ?? []);
                break;
            case 'NOTICE':
                $this->logger->notice($message, $context ?? []);
                break;
            case 'WARNING':
                $this->logger->warning($message, $context ?? []);
                break;
            case 'ERROR':
                $this->logger->error($message, $context ?? []);
                break;
            case 'CRITICAL':
                $this->logger->critical($message, $context ?? []);
                break;
            case 'ALERT':
                $this->logger->alert($message, $context ?? []);
                break;
            case 'EMERGENCY':
                $this->logger->emergency($message, $context ?? []);
                break;
        }
    }

    // Convenience methods for common log types
    public function logLoginSuccess(User $user): void
    {
        $this->log(
            'LOGIN_SUCCESS',
            sprintf('User %s logged in successfully', $user->getEmail()),
            'INFO',
            ['userId' => $user->getUserId()]
        );
    }

    public function logLoginFailure(string $email, string $reason): void
    {
        $this->log(
            'LOGIN_FAILED',
            sprintf('Failed login attempt for email %s: %s', $email, $reason),
            'WARNING',
            ['email' => $email]
        );
    }

    public function logLogout(User $user): void
    {
        $this->log(
            'LOGOUT',
            sprintf('User %s logged out', $user->getEmail()),
            'INFO',
            ['userId' => $user->getUserId()]
        );
    }

    public function logRoleChange(User $user, string $oldRole, string $newRole): void
    {
        $this->log(
            'ROLE_CHANGE',
            sprintf('User %s role changed from %s to %s', $user->getEmail(), $oldRole, $newRole),
            'INFO',
            [
                'userId' => $user->getUserId(),
                'oldRole' => $oldRole,
                'newRole' => $newRole
            ]
        );
    }

    public function logEventAction(string $action, string $eventTitle, ?User $user = null): void
    {
        $this->log(
            'EVENT_' . $action,
            sprintf('Event "%s" was %s', $eventTitle, strtolower($action)),
            'INFO',
            [
                'eventTitle' => $eventTitle,
                'userId' => $user?->getUserId()
            ]
        );
    }
} 