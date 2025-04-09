<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs')]
#[IsGranted('ROLE_ADMIN')]
class LogController extends AbstractController
{
    #[Route('/', name: 'app_admin_logs_index', methods: ['GET'])]
    public function index(LogRepository $logRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $logs = $logRepository->findRecentLogs($limit);
        $totalLogs = $logRepository->count([]);
        $totalPages = ceil($totalLogs / $limit);

        return $this->render('admin/log/index.html.twig', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/user/{id}', name: 'app_admin_logs_user', methods: ['GET'])]
    public function userLogs(LogRepository $logRepository, int $id, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $logs = $logRepository->findLogsByUser($id, $limit);
        $totalLogs = $logRepository->count(['user' => $id]);
        $totalPages = ceil($totalLogs / $limit);

        return $this->render('admin/log/user.html.twig', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'userId' => $id,
        ]);
    }

    #[Route('/failed-logins', name: 'app_admin_logs_failed_logins', methods: ['GET'])]
    public function failedLogins(LogRepository $logRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $logs = $logRepository->findFailedLoginAttempts($limit);
        $totalLogs = $logRepository->count(['action' => 'LOGIN_FAILED']);
        $totalPages = ceil($totalLogs / $limit);

        return $this->render('admin/log/failed_logins.html.twig', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_logs_show', methods: ['GET'])]
    public function show(Log $log): Response
    {
        return $this->render('admin/log/show.html.twig', [
            'log' => $log,
        ]);
    }
} 