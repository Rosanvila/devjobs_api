<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Ne traiter que les requêtes API
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $statusCode = 500;
        $message = 'Internal Server Error';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        // Messages d'erreur personnalisés
        $errorMessages = [
            400 => 'Bad Request - Données invalides',
            401 => 'Unauthorized - Authentification requise',
            403 => 'Forbidden - Accès refusé',
            404 => 'Not Found - Ressource introuvable',
            405 => 'Method Not Allowed - Méthode HTTP non autorisée',
            422 => 'Unprocessable Entity - Données non traitées',
            429 => 'Too Many Requests - Trop de requêtes',
            500 => 'Internal Server Error - Erreur serveur',
        ];

        $response = new JsonResponse([
            'error' => [
                'code' => $statusCode,
                'message' => $errorMessages[$statusCode] ?? $message,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod()
            ]
        ], $statusCode);

        $event->setResponse($response);
    }
}
