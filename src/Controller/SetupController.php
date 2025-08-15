<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/setup')]
class SetupController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    ) {}

    #[Route('/create-admin', methods: ['POST'])]
    public function createAdmin(): JsonResponse
    {
        try {
            // Créer un utilisateur admin
            $admin = $this->authService->createUser(
                'admin@devjobs.com',
                'admin123',
                'Admin',
                'DevJobs',
                ['ROLE_ADMIN', 'ROLE_USER']
            );

            return new JsonResponse([
                'message' => 'Administrateur créé avec succès',
                'user' => $admin->toArray(),
                'credentials' => [
                    'email' => 'admin@devjobs.com',
                    'password' => 'admin123'
                ]
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/test-auth', methods: ['GET'])]
    public function testAuth(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Authentification configurée avec succès',
            'endpoints' => [
                'POST /api/auth/register' => 'Créer un compte',
                'POST /api/auth/login' => 'Se connecter',
                'POST /api/auth/logout' => 'Se déconnecter',
                'POST /api/auth/refresh' => 'Rafraîchir le token',
                'GET /api/auth/me' => 'Profil utilisateur',
                'POST /api/auth/change-password' => 'Changer le mot de passe'
            ],
            'usage' => [
                '1. Créer un admin avec POST /api/setup/create-admin',
                '2. Se connecter avec POST /api/auth/login',
                '3. Utiliser le token dans l\'en-tête Authorization: Bearer <token>'
            ]
        ], 200);
    }
}
