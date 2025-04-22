<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            )
        );

        // Validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/profile/update', name: 'profile_update', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['password'])) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $data['password']
                )
            );
        }

        // Validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ]
        ]);
    }

    #[Route('/docs', name: 'api_docs', methods: ['GET'])]
    public function docs(): JsonResponse
    {
        return $this->json([
            'endpoints' => [
                'POST /api/login_check' => [
                    'description' => 'Get JWT token',
                    'parameters' => [
                        'email' => 'string (required)',
                        'password' => 'string (required)'
                    ],
                    'returns' => [
                        'token' => 'JWT token string'
                    ]
                ],
                'POST /api/register' => [
                    'description' => 'Register a new user',
                    'parameters' => [
                        'email' => 'string (required)',
                        'password' => 'string (required)',
                        'firstName' => 'string (required)',
                        'lastName' => 'string (required)'
                    ]
                ],
                'GET /api/profile' => [
                    'description' => 'Get user profile',
                    'authentication' => 'Bearer token required',
                    'returns' => [
                        'user' => [
                            'id' => 'integer',
                            'email' => 'string',
                            'firstName' => 'string',
                            'lastName' => 'string',
                            'roles' => 'array'
                        ]
                    ]
                ],
                'PUT /api/profile/update' => [
                    'description' => 'Update user profile',
                    'authentication' => 'Bearer token required',
                    'parameters' => [
                        'firstName' => 'string (optional)',
                        'lastName' => 'string (optional)',
                        'password' => 'string (optional)'
                    ]
                ]
            ]
        ]);
    }
}