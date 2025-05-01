<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Rest\Client;

class RegistrationController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ValidatorInterface $validator,
        MailerInterface $mailer
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        
        if ($request->isMethod('POST')) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            $recaptchaSecret = $this->getParameter('GOOGLE_RECAPTCHA_SECRET');

            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $recaptchaSecret,
                    'response' => $recaptchaResponse,
                ],
            ]);

            $recaptchaData = $response->toArray();

            if (!$recaptchaData['success']) {
                return $this->render('registration/register.html.twig', [
                    'errorMessages' => ['recaptcha' => ['Invalid reCAPTCHA. Please try again.']],
                    'lastInput' => $request->request->all()
                ]);
            }

            $user->setEmail($request->request->get('email'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setPhoneNumber($request->request->get('phoneNumber'));
            
            // Set default role as ROLE_USER
            $user->setRoles(['ROLE_USER']);
            
            // Hash the password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )
            );

            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
                }

                // Debugging: Log validation errors
                $this->addFlash('error', 'Validation errors occurred. Please check the form.');
                foreach ($errorMessages as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addFlash('error', "$field: $message");
                    }
                }

                return $this->render('registration/register.html.twig', [
                    'errorMessages' => $errorMessages,
                    'lastInput' => $request->request->all()
                ]);
            }

            // Generate verification token
            $user->setVerificationToken($slugger->slug(uniqid()));
            
            $entityManager->persist($user);
            $entityManager->flush();

            // Send confirmation email
            $email = (new Email())
                ->from('no-reply@mythosplanner.com')
                ->to($user->getEmail())
                ->subject('Confirm Your Email Address')
                ->html('<p>Thank you for registering!</p>
                       <p>Please confirm your email address by clicking the link below:</p>
                       <a href="' . $this->generateUrl('app_verify_email', ['token' => $user->getVerificationToken()], UrlGeneratorInterface::ABSOLUTE_URL) . '">Confirm Email</a>');

            $mailer->send($email);

            // Send SMS notification
            $twilio = new Client(
                $this->getParameter('TWILIO_ACCOUNT_SID'),
                $this->getParameter('TWILIO_AUTH_TOKEN')
            );

            $twilio->messages->create(
                $user->getPhoneNumber(), // Use the user's phone number
                [
                    'from' => $this->getParameter('TWILIO_PHONE_NUMBER'),
                    'body' => 'Thank you for registering with Mythos Planner! Please check your email to verify your account.'
                ]
            );

            $this->addFlash('success', 'Registration successful! Please check your email and SMS for confirmation.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }

    #[Route('/verify/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'verificationToken' => $token
        ]);

        if (!$user) {
            throw $this->createNotFoundException('Invalid verification token');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Your email address has been verified. You can now log in.');
        return $this->redirectToRoute('app_login');
    }
}
