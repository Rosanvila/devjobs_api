<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    ) {}

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        $constraint = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(['message' => 'L\'email est requis']),
                new Assert\Email(['message' => 'L\'email n\'est pas valide'])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Le mot de passe est requis']),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                ])
            ]
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $result = $this->authService->authenticate($data['email'], $data['password']);
            
            return new JsonResponse($result, 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 401);
        }
    }

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        $constraint = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(['message' => 'L\'email est requis']),
                new Assert\Email(['message' => 'L\'email n\'est pas valide'])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Le mot de passe est requis']),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                ])
            ],
            'firstName' => [
                new Assert\NotBlank(['message' => 'Le prénom est requis']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                    'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères'
                ])
            ],
            'lastName' => [
                new Assert\NotBlank(['message' => 'Le nom est requis']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                    'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                ])
            ]
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $user = $this->authService->createUser(
                $data['email'],
                $data['password'],
                $data['firstName'],
                $data['lastName']
            );

            return new JsonResponse([
                'message' => 'Utilisateur créé avec succès',
                'user' => $user->toArray()
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token) {
            return new JsonResponse([
                'error' => 'Token d\'authentification requis'
            ], 401);
        }

        $user = $this->authService->validateToken($token);
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'Token invalide ou expiré'
            ], 401);
        }

        $this->authService->logout($user);

        return new JsonResponse([
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    #[Route('/refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token) {
            return new JsonResponse([
                'error' => 'Token d\'authentification requis'
            ], 401);
        }

        $user = $this->authService->validateToken($token);
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'Token invalide ou expiré'
            ], 401);
        }

        try {
            $result = $this->authService->refreshToken($user);
            
            return new JsonResponse($result, 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token) {
            return new JsonResponse([
                'error' => 'Token d\'authentification requis'
            ], 401);
        }

        $user = $this->authService->validateToken($token);
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'Token invalide ou expiré'
            ], 401);
        }

        return new JsonResponse([
            'user' => $user->toArray()
        ], 200);
    }

    #[Route('/change-password', methods: ['POST'])]
    public function changePassword(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token) {
            return new JsonResponse([
                'error' => 'Token d\'authentification requis'
            ], 401);
        }

        $user = $this->authService->validateToken($token);
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'Token invalide ou expiré'
            ], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Validation des données
        $constraint = new Assert\Collection([
            'currentPassword' => [
                new Assert\NotBlank(['message' => 'Le mot de passe actuel est requis'])
            ],
            'newPassword' => [
                new Assert\NotBlank(['message' => 'Le nouveau mot de passe est requis']),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le nouveau mot de passe doit contenir au moins {{ limit }} caractères'
                ])
            ]
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            // Vérifier l'ancien mot de passe
            if (!$this->authService->validatePassword($user, $data['currentPassword'])) {
                return new JsonResponse([
                    'error' => 'Le mot de passe actuel est incorrect'
                ], 400);
            }

            $this->authService->changePassword($user, $data['newPassword']);

            return new JsonResponse([
                'message' => 'Mot de passe modifié avec succès'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

