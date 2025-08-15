<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Authentifie un utilisateur et génère un token
     */
    public function authenticate(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new AuthenticationException('Email ou mot de passe incorrect');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Email ou mot de passe incorrect');
        }

        $token = $user->generateNewToken();
        $this->userRepository->save($user, true);

        return [
            'user' => $user->toArray(),
            'token' => $token,
            'expiresAt' => $user->getTokenExpiresAt()->format('Y-m-d H:i:s'),
            'message' => 'Connexion réussie'
        ];
    }

    /**
     * Valide un token Bearer et retourne l'utilisateur
     */
    public function validateToken(string $token): ?User
    {
        if (empty($token)) {
            return null;
        }

        $token = str_replace('Bearer ', '', $token);
        $user = $this->userRepository->findActiveByValidToken($token);

        return $user;
    }

    /**
     * Déconnecte un utilisateur en invalidant son token
     */
    public function logout(User $user): void
    {
        $user->invalidateToken();
        $this->userRepository->save($user, true);
    }

    /**
     * Rafraîchit le token d'un utilisateur
     */
    public function refreshToken(User $user): array
    {
        $token = $user->generateNewToken();
        $this->userRepository->save($user, true);

        return [
            'user' => $user->toArray(),
            'token' => $token,
            'expiresAt' => $user->getTokenExpiresAt()->format('Y-m-d H:i:s'),
            'message' => 'Token rafraîchi avec succès'
        ];
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function createUser(string $email, string $password, array $roles = ['ROLE_USER']): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('Un utilisateur avec cet email existe déjà');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);

        $this->userRepository->save($user, true);
        return $user;
    }

    /**
     * Change le mot de passe d'un utilisateur
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $this->userRepository->save($user, true);
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanExpiredTokens(): int
    {
        return $this->userRepository->cleanExpiredTokens();
    }

    /**
     * Valide un mot de passe pour un utilisateur
     */
    public function validatePassword(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    /**
     * Vérifie si un utilisateur a un rôle spécifique
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Ajoute un rôle à un utilisateur
     */
    public function addRole(User $user, string $role): void
    {
        $user->addRole($role);
        $this->userRepository->save($user, true);
    }

    /**
     * Supprime un rôle d'un utilisateur
     */
    public function removeRole(User $user, string $role): void
    {
        $user->removeRole($role);
        $this->userRepository->save($user, true);
    }

    /**
     * Génère un token sécurisé avec une durée personnalisable
     */
    public function generateSecureToken(User $user, int $hours = 24): string
    {
        $token = $user->generateNewToken();

        if ($hours !== 24) {
            $user->setTokenExpiresAt((new \DateTime())->modify("+{$hours} hours"));
        }

        $this->userRepository->save($user, true);
        return $token;
    }

    /**
     * Vérifie si un token est sur le point d'expirer (dans les 2h)
     */
    public function isTokenExpiringSoon(User $user): bool
    {
        if (!$user->getTokenExpiresAt()) {
            return true;
        }

        $twoHoursFromNow = (new \DateTime())->modify('+2 hours');
        return $user->getTokenExpiresAt() <= $twoHoursFromNow;
    }
}
