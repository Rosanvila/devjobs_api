<?php

namespace App\EventListener;

use App\Attribute\RequiresAuth;
use App\Service\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationListener implements EventSubscriberInterface
{
    public function __construct(
        private AuthService $authService,
        private RouterInterface $router
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $reflection = new \ReflectionClass($controller);
        $method = $event->getRequest()->getMethod();

        // Vérifier l'attribut sur la classe
        $classAttribute = $reflection->getAttributes(RequiresAuth::class)[0] ?? null;

        // Vérifier l'attribut sur la méthode
        $methodAttribute = null;
        if (method_exists($controller, $method)) {
            $methodReflection = $reflection->getMethod($method);
            $methodAttribute = $methodReflection->getAttributes(RequiresAuth::class)[0] ?? null;
        }

        // L'attribut sur la méthode a la priorité
        $attribute = $methodAttribute ?? $classAttribute;

        if (!$attribute) {
            return; // Pas d'authentification requise
        }

        $requiresAuth = $attribute->newInstance();
        $requiredRoles = $requiresAuth->roles;

        $request = $event->getRequest();
        $token = $request->headers->get('Authorization');

        if (!$token) {
            throw new UnauthorizedHttpException('Bearer', 'Token d\'authentification requis');
        }

        $user = $this->authService->validateToken($token);

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Token invalide ou expiré');
        }

        // Vérifier les rôles
        $userRoles = $user->getRoles();
        $hasRequiredRole = false;

        foreach ($requiredRoles as $requiredRole) {
            if (in_array($requiredRole, $userRoles)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            throw new AccessDeniedHttpException('Accès refusé : rôles insuffisants');
        }

        // Ajouter l'utilisateur à la requête pour qu'il soit accessible dans le contrôleur
        $request->attributes->set('_user', $user);
    }
}
