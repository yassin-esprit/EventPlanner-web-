<?php

namespace App\Controller;

use App\Entity\RoleRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_admin_dashboard')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get role requests
        $roleRequests = $this->entityManager->getRepository(RoleRequest::class)->findBy(
            ['status' => 'pending'],
            ['requestedAt' => 'DESC']
        );

        // Get users with search and sorting
        $queryBuilder = $this->entityManager->getRepository(User::class)->createQueryBuilder('u');
        
        // Search functionality
        if ($search = $request->query->get('search')) {
            $queryBuilder
                ->where('u.email LIKE :search')
                ->orWhere('u.firstName LIKE :search')
                ->orWhere('u.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sorting
        $sortField = $request->query->get('sort', 'id');
        $sortOrder = $request->query->get('order', 'ASC');
        $queryBuilder->orderBy('u.' . $sortField, $sortOrder);

        // Pagination
        $users = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        // Get login logs
        $loginLogs = $this->getLoginLogs();

        return $this->render('admin/index.html.twig', [
            'roleRequests' => $roleRequests,
            'users' => $users,
            'loginLogs' => $loginLogs,
            'currentSort' => $sortField,
            'currentOrder' => $sortOrder
        ]);
    }

    #[Route('/role-request/{id}/process', name: 'app_admin_process_role_request', methods: ['POST'])]
    public function processRoleRequest(Request $request, RoleRequest $roleRequest): Response
    {
        $status = $request->request->get('status');
        $comment = $request->request->get('comment');

        $roleRequest->setStatus($status);
        $roleRequest->setComment($comment);
        $roleRequest->setProcessedAt(new \DateTime());

        $this->entityManager->persist($roleRequest);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Role request processed successfully'
            ]);
        }

        $this->addFlash('success', 'Role request processed successfully');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/new', name: 'app_admin_new_user', methods: ['GET', 'POST'])]
    public function newUser(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setRoles([$request->request->get('role')]);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )
            );
            $user->setIsVerified(true);

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', 'User created successfully.');
                return $this->redirectToRoute('app_admin_dashboard');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'The email address is already in use. Please use a different email.');
            }
        }

        return $this->render('admin/new_user.html.twig');
    }

    #[Route('/users/{id}/edit', name: 'app_admin_edit_user', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user): Response
    {
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setRoles([$request->request->get('role')]);
            
            if ($password = $request->request->get('password')) {
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, $password)
                );
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/edit_user.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/users/export', name: 'app_admin_export_users')]
    public function exportUsersPDF(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        // Generate PDF content
        $html = $this->renderView('admin/users_pdf.html.twig', [
            'users' => $users
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate response
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename=users.pdf');

        return $response;
    }

    #[Route('/role-requests', name: 'app_admin_role_requests')]
    public function listRoleRequests(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $queryBuilder = $this->entityManager->getRepository(RoleRequest::class)->createQueryBuilder('r')
            ->orderBy('r.requestedAt', 'DESC');

        // Filtering by status
        if ($status = $request->query->get('status')) {
            $queryBuilder->andWhere('r.status = :status')->setParameter('status', $status);
        }

        // Pagination
        $roleRequests = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/role_requests.html.twig', [
            'roleRequests' => $roleRequests,
            'currentStatus' => $status
        ]);
    }

    #[Route('/role-requests/{id}/approve', name: 'app_admin_approve_role_request', methods: ['POST'])]
    public function approveRoleRequest(RoleRequest $roleRequest): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $roleRequest->setStatus('approved');
        $roleRequest->setProcessedAt(new \DateTimeImmutable());
        $roleRequest->setProcessedBy($this->getUser());

        $user = $roleRequest->getUser();
        $user->setRoles([$roleRequest->getRequestedRole()]);

        $this->entityManager->flush();

        $this->addFlash('success', 'Role request approved successfully.');
        return $this->redirectToRoute('app_admin_role_requests');
    }

    #[Route('/role-requests/{id}/reject', name: 'app_admin_reject_role_request', methods: ['POST'])]
    public function rejectRoleRequest(Request $request, RoleRequest $roleRequest): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reason = $request->request->get('reason');
        $roleRequest->setStatus('rejected');
        $roleRequest->setProcessedAt(new \DateTimeImmutable());
        $roleRequest->setProcessedBy($this->getUser());
        $roleRequest->setReason($reason);

        $this->entityManager->flush();

        $this->addFlash('success', 'Role request rejected successfully.');
        return $this->redirectToRoute('app_admin_role_requests');
    }

    private function getLoginLogs(): array
    {
        // Read the last 50 lines of the security.log file
        $logFile = $this->getParameter('kernel.logs_dir') . '/security.log';
        $logs = [];

        if (file_exists($logFile)) {
            $file = new \SplFileObject($logFile);
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();

            $start = max(0, $lastLine - 50);
            $file->seek($start);

            while (!$file->eof()) {
                $line = $file->current();
                if (strpos($line, 'security.INFO') !== false) {
                    // Extract timestamp, action, and username
                    preg_match('/\[(.*?)\] security\.INFO: (.*?) \{"token":\{".*?user=\\"(.*?)\\"/', $line, $matches);
                    if (count($matches) === 4) {
                        $logs[] = [
                            'timestamp' => $matches[1],
                            'action' => $matches[2],
                            'username' => $matches[3]
                        ];
                    }
                }
                $file->next();
            }
        }

        return array_reverse($logs);
    }
}
