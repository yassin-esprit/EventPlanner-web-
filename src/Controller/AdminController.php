<?php

namespace App\Controller;

use App\Entity\RoleRequest;
use App\Entity\User;
use App\Entity\Merchandise;
use App\Repository\MerchandiseRepository;
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
use App\Entity\Donation;

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

    #[Route('/', name: 'app_admin_panel')]
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

        $donations = $this->entityManager->getRepository(Donation::class)->findBy(['status' => 'pending']);

        return $this->render('admin/index.html.twig', [
            'roleRequests' => $roleRequests,
            'users' => $users,
            'loginLogs' => $loginLogs,
            'currentSort' => $sortField,
            'currentOrder' => $sortOrder,
            'donations' => $donations
        ]);
    }

    #[Route('/role-request/{id}/process', name: 'app_admin_process_role_request', methods: ['POST'])]
    public function processRoleRequest(Request $request, RoleRequest $roleRequest, MailerInterface $mailer): Response
    {
        $status = $request->request->get('status');
        $comment = $request->request->get('comment');

        if (!$status || !in_array($status, ['accepted', 'denied'])) {
            $this->addFlash('error', 'Invalid status provided.');
            return $this->redirectToRoute('app_admin_panel');
        }

        $roleRequest->setStatus($status);
        $roleRequest->setComment($comment);
        $roleRequest->setProcessedAt(new \DateTimeImmutable());
        $roleRequest->setProcessedBy($this->getUser());

        $this->entityManager->persist($roleRequest);
        $this->entityManager->flush();

        // Send email notification to the user
        $email = (new Email())
            ->from('no-reply@mythosplanner.com')
            ->to($roleRequest->getUser()->getEmail())
            ->subject('Role Request Update')
            ->html($this->renderView('emails/role_request_update.html.twig', [
                'roleRequest' => $roleRequest,
                'baseUrl' => $this->getParameter('app.base_url')
            ]));

        $mailer->send($email);

        $this->addFlash('success', 'Role request processed successfully.');
        return $this->redirectToRoute('app_admin_panel');
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

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('app_admin_panel');
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
            return $this->redirectToRoute('app_admin_panel');
        }

        return $this->render('admin/edit_user.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_delete_user', methods: ['POST'])]
    public function deleteUser(User $user): Response
    {
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_panel');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin_panel');
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

    #[Route('/merchandise', name: 'admin_merchandise')]
    public function merchandiseList(MerchandiseRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search');
        $merchandise = $repository->findBySearch($search);

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('admin/_merchandise_list_content.html.twig', [
                'merchandise' => $merchandise,
            ]);
        }

        return $this->render('admin/merchandise_list.html.twig', [
            'merchandise' => $merchandise,
        ]);
    }

    #[Route('/merchandise/add', name: 'admin_add_merchandise')]
    public function addMerchandise(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $merchandise = new Merchandise();
            $merchandise->setName($request->request->get('name'));
            $merchandise->setDescription($request->request->get('description'));
            $merchandise->setPrice($request->request->get('price'));
            $merchandise->setQuantity($request->request->get('quantity'));

            $file = $request->files->get('image');
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('merchandise_images_directory'),
                    $fileName
                );
                $merchandise->setImage($fileName);
            }

            $entityManager->persist($merchandise);
            $entityManager->flush();

            return $this->redirectToRoute('admin_merchandise');
        }

        return $this->render('admin/add_merchandise.html.twig');
    }

    #[Route('/merchandise/delete/{id}', name: 'admin_delete_merchandise')]
    public function deleteMerchandise(Merchandise $merchandise, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($merchandise);
        $entityManager->flush();

        return $this->redirectToRoute('admin_merchandise');
    }

    #[Route('/admin/merchandise/export', name: 'admin_export_merchandise')]
    public function exportMerchandisePDF(MerchandiseRepository $repository): Response
    {
        $merchandise = $repository->findAll();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Generate PDF content
        $html = $this->renderView('admin/merchandise_pdf.html.twig', [
            'merchandise' => $merchandise
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate response
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename=merchandise.pdf');

        return $response;
    }

    #[Route('/admin/merchandise/update/{id}', name: 'admin_update_merchandise')]
    public function updateMerchandise(Request $request, Merchandise $merchandise, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $merchandise->setName($request->request->get('name'));
            $merchandise->setDescription($request->request->get('description'));
            $merchandise->setPrice($request->request->get('price'));
            $merchandise->setQuantity($request->request->get('quantity'));

            $file = $request->files->get('image');
            if ($file) {
                // Delete old image if it exists
                $oldImage = $merchandise->getImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('merchandise_images_directory') . '/' . $oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Upload new image
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('merchandise_images_directory'),
                    $fileName
                );
                $merchandise->setImage($fileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Merchandise updated successfully.');
            return $this->redirectToRoute('admin_merchandise');
        }

        return $this->render('admin/update_merchandise.html.twig', [
            'merchandise' => $merchandise
        ]);
    }

    #[Route('/donations', name: 'app_admin_donations')]
    public function donations(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $donations = $entityManager->getRepository(Donation::class)->findBy(
            ['status' => 'pending'],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/donations.html.twig', [
            'donations' => $donations
        ]);
    }

    #[Route('/donation/{id}/process', name: 'app_admin_process_donation', methods: ['POST'])]
    public function processDonation(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $status = $request->request->get('status');

        if (!$status || !in_array($status, ['accepted', 'denied'])) {
            $this->addFlash('error', 'Invalid status provided.');
            return $this->redirectToRoute('app_admin_donations');
        }

        $donation->setStatus($status);
        $donation->setProcessedAt(new \DateTimeImmutable());
        $donation->setProcessedBy($this->getUser());

        $entityManager->flush();

        $this->addFlash('success', 'Donation has been ' . $status . '.');
        return $this->redirectToRoute('app_admin_donations');
    }

    private function getLoginLogs(): array
    {
        // Read the last 50 lines of the dev.log file
        $logFile = $this->getParameter('kernel.logs_dir') . '/dev.log';
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
                    $logs[] = [
                        'timestamp' => substr($line, 0, 19),
                        'message' => trim(substr($line, strpos($line, ']') + 1))
                    ];
                }
                $file->next();
            }
        }

        return array_reverse($logs);
    }
}
