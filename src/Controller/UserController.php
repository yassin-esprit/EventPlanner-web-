<?php

namespace App\Controller;

use App\Entity\RoleRequest;
use App\Entity\Merchandise;
use App\Repository\MerchandiseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Donation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/user')]
class UserController extends AbstractController
{
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
    public function requestRole(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, ValidatorInterface $validator): Response
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

        $roleRequest = new RoleRequest();
        $roleRequest->setUser($user);
        $roleRequest->setRequestedRole($requestedRole);
        $roleRequest->setReason($reason);

        // Validate the role request entity
        $errors = $validator->validate($roleRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($roleRequest);
        $entityManager->flush();

        // Send email notification to admin
        $email = (new Email())
            ->from('no-reply@mythosplanner.com')
            ->to('admin@mythosplanner.com')
            ->subject('New Role Request')
            ->html('<p>User ' . $user->getFirstName() . ' ' . $user->getLastName() . ' has requested the role: ' . $requestedRole . '.</p>');

        $mailer->send($email);

        $this->addFlash('success', 'Your role request has been submitted and is pending approval.');
        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/request-role-page', name: 'app_request_role_page', methods: ['GET'])]
    public function requestRolePage(): Response
    {
        return $this->render('user/request_role.html.twig');
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
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
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

        // Validate the user entity
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

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

    #[Route('/merchandise', name: 'user_merchandise')]
    public function merchandiseList(MerchandiseRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $repository->createQueryBuilder('m');
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Current page number
            10 // Items per page
        );

        return $this->render('user/merchandise_list.html.twig', [
            'merchandise' => $pagination,
        ]);
    }

    #[Route('/merchandise/buy/{id}', name: 'user_buy_merchandise')]
    public function buyMerchandise(Merchandise $merchandise, EntityManagerInterface $entityManager): Response
    {
        if ($merchandise->getQuantity() > 0) {
            $merchandise->setQuantity($merchandise->getQuantity() - 1);
            $entityManager->flush();

            $this->addFlash('success', 'Purchase successful!');
        } else {
            $this->addFlash('error', 'This item is out of stock.');
        }

        return $this->redirectToRoute('user_merchandise');
    }

    #[Route('/submit-donation', name: 'app_user_submit_donation', methods: ['POST'])]
    public function submitDonation(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $message = $request->request->get('message');

        $donation = new Donation();
        $donation->setUser($user);
        $donation->setMessage($message);

        // Validate the donation entity
        $errors = $validator->validate($donation);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($donation);
        $entityManager->flush();

        $this->addFlash('success', 'Your donation has been submitted and is pending approval.');
        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/update-role/{id}', name: 'app_user_update_role', methods: ['GET', 'POST'])]
    public function updateRole(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        if ($request->isMethod('POST')) {
            $newRole = $request->request->get('role');

            if (!in_array($newRole, ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_USER'])) {
                $this->addFlash('error', 'Invalid role selected.');
                return $this->redirectToRoute('app_user_update_role', ['id' => $id]);
            }

            $user->setRoles([$newRole]);
            $entityManager->flush();

            $this->addFlash('success', 'Role updated successfully.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('user/update_role.html.twig', [
            'user' => $user,
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_USER']
        ]);
    }
}
