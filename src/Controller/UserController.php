<?php

namespace App\Controller;

use App\Entity\RoleRequest;
use App\Entity\User;
use App\Entity\Merchandise;
use App\Entity\Purchase;
use App\Entity\Donation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/dashboard', name: 'app_user_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        $pendingRequest = $entityManager->getRepository(RoleRequest::class)->findOneBy([
            'user' => $user,
            'status' => 'pending'
        ]);

        $roleHistory = $entityManager->getRepository(RoleRequest::class)->findBy(
            ['user' => $user],
            ['requestedAt' => 'DESC']
        );

        return $this->render('user/dashboard.html.twig', [
            'pendingRequest' => $pendingRequest,
            'roleHistory' => $roleHistory
        ]);
    }

    #[Route('/request-role', name: 'app_request_role', methods: ['POST'])]
    public function requestRole(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Check if user already has a pending request
        $existingRequest = $entityManager->getRepository(RoleRequest::class)->findOneBy([
            'user' => $user,
            'status' => 'pending'
        ]);

        if ($existingRequest) {
            $this->addFlash('error', 'You already have a pending role request.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        $requestedRole = $request->request->get('role');
        $reason = $request->request->get('reason');

        // Validate requested role
        $allowedRoles = ['ROLE_OWNER'];
        if (!in_array($requestedRole, $allowedRoles)) {
            $this->addFlash('error', 'Invalid role requested.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        $roleRequest = new RoleRequest();
        $roleRequest->setUser($user);
        $roleRequest->setRequestedRole($requestedRole);
        $roleRequest->setReason($reason);

        $entityManager->persist($roleRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Your role request has been submitted and is pending approval.');
        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/settings', name: 'app_user_settings')]
    public function settings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        return $this->render('user/settings.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/settings/update', name: 'app_user_settings_update', methods: ['POST'])]
    public function updateSettings(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        // Validate email uniqueness
        $newEmail = $request->request->get('email');
        if ($newEmail !== $user->getEmail()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $newEmail]);
            if ($existingUser) {
                $this->addFlash('error', 'This email is already in use.');
                return $this->redirectToRoute('app_user_settings');
            }
        }

        // Update basic information
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setEmail($newEmail);

        // Handle password change if requested
        $currentPassword = $request->request->get('currentPassword');
        $newPassword = $request->request->get('newPassword');
        $confirmPassword = $request->request->get('confirmPassword');

        if ($currentPassword && $newPassword && $confirmPassword) {
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('app_user_settings');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'New passwords do not match.');
                return $this->redirectToRoute('app_user_settings');
            }

            // Validate password strength
            if (strlen($newPassword) < 8) {
                $this->addFlash('error', 'Password must be at least 8 characters long.');
                return $this->redirectToRoute('app_user_settings');
            }

            if (!preg_match('/[A-Z]/', $newPassword) || 
                !preg_match('/[a-z]/', $newPassword) || 
                !preg_match('/[0-9]/', $newPassword)) {
                $this->addFlash('error', 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
                return $this->redirectToRoute('app_user_settings');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }

        try {
            $entityManager->flush();
            $this->addFlash('success', 'Your settings have been updated successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while saving your changes. Please try again.');
        }
        
        return $this->redirectToRoute('app_user_profile');
    }

    #[Route('/merchandise', name: 'app_user_merchandise')]
    public function merchandise(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $queryBuilder = $this->entityManager->getRepository(Merchandise::class)->createQueryBuilder('m')
            ->where('m.quantity > 0');

        // Search functionality
        if ($search = $request->query->get('search')) {
            $queryBuilder
                ->andWhere('m.name LIKE :search OR m.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sorting
        $sortField = $request->query->get('sort', 'name');
        $sortOrder = $request->query->get('order', 'ASC');
        $queryBuilder->orderBy('m.' . $sortField, $sortOrder);

        // Pagination
        $merchandise = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('user/merchandise.html.twig', [
            'merchandise' => $merchandise,
            'currentSort' => $sortField,
            'currentOrder' => $sortOrder
        ]);
    }

    #[Route('/merchandise/{id}/purchase', name: 'app_user_purchase_merchandise', methods: ['POST'])]
    public function purchaseMerchandise(Request $request, Merchandise $merchandise, MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        // Check if merchandise is still available
        if ($merchandise->getQuantity() <= 0) {
            $this->addFlash('error', 'This item is no longer available.');
            return $this->redirectToRoute('app_user_merchandise');
        }

        // Create purchase record
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setMerchandise($merchandise);
        $purchase->setPurchasePrice($merchandise->getPrice());

        // Update merchandise quantity
        $merchandise->setQuantity($merchandise->getQuantity() - 1);

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        // Generate PDF receipt
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $html = $this->renderView('user/purchase_receipt.html.twig', [
            'purchase' => $purchase
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Send email with PDF receipt
        $email = (new Email())
            ->from('your-email@example.com')
            ->to($user->getEmail())
            ->subject('Purchase Confirmation')
            ->html($this->renderView('emails/purchase_confirmation.html.twig', [
                'purchase' => $purchase
            ]))
            ->attach($dompdf->output(), 'purchase_receipt.pdf', 'application/pdf');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Log the error but don't stop the purchase process
        }

        $this->addFlash('success', 'Purchase successful! Check your email for the receipt.');
        return $this->redirectToRoute('app_user_merchandise');
    }

    #[Route('/donate', name: 'app_user_donate', methods: ['GET', 'POST'])]
    public function donate(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            $amount = floatval($request->request->get('amount'));
            
            if ($amount <= 0) {
                $this->addFlash('error', 'Please enter a valid donation amount.');
                return $this->redirectToRoute('app_user_donate');
            }

            $donation = new Donation();
            $donation->setUser($this->getUser());
            $donation->setAmount($amount);

            $this->entityManager->persist($donation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your donation request has been submitted and is pending approval.');
            return $this->redirectToRoute('app_user_donations');
        }

        return $this->render('user/donate.html.twig');
    }

    #[Route('/donations', name: 'app_user_donations')]
    public function donations(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $queryBuilder = $this->entityManager->getRepository(Donation::class)->createQueryBuilder('d')
            ->where('d.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('d.requestedAt', 'DESC');

        $donations = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/donations.html.twig', [
            'donations' => $donations
        ]);
    }
}
