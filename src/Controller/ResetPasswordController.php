<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reset_password_request', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->redirectToRoute('app_forgot_password_request');
            }

            $email = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $entityManager->flush();

                $resetLink = $this->generateUrl('app_reset_password', ['token' => $token], true);
                
                $email = (new Email())
                    ->from('mythos.official540@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html(
                        $this->renderView('emails/reset_password.html.twig', [
                            'resetLink' => $resetLink,
                            'userName' => $user->getFirstName()
                        ])
                    );

                $mailer->send($email);
            }

            // Don't reveal whether a user account exists or not
            $this->addFlash('success', 'If an account exists for this email, you will receive a password reset link');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request, 
        string $token, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'resetToken' => $token,
        ]);

        if (!$user || !$user->getResetTokenExpiresAt() || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'The password reset link is invalid or has expired.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reset_password', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            // Validate password
            $constraints = new Assert\Collection([
                'password' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 8]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                        'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'
                    ])
                ]
            ]);

            $violations = $validator->validate(['password' => $password], $constraints);

            if (count($violations) > 0) {
                $this->addFlash('error', $violations->get(0)->getMessage());
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Your password has been successfully reset');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig');
    }
}