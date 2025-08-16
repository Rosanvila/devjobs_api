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
}
