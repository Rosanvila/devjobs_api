<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Trouve un utilisateur par son token d'authentification
     */
    public function findByToken(string $token): ?User
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Trouve un utilisateur par son token valide
     */
    public function findActiveByValidToken(string $token): ?User
    {
        $user = $this->findByToken($token);

        if (!$user || !$user->isTokenValid()) {
            return null;
        }

        return $user;
    }

    /**
     * Trouve tous les utilisateurs avec un rôle spécifique
     */
    public function findByRole(string $role): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve tous les utilisateurs
     */
    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanExpiredTokens(): int
    {
        $qb = $this->createQueryBuilder('u');
        $qb->update(User::class, 'u')
            ->set('u.token', ':null')
            ->set('u.tokenExpiresAt', ':null')
            ->where('u.tokenExpiresAt < :now')
            ->setParameter('null', null)
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->execute();
    }

    /**
     * Trouve les utilisateurs avec des tokens expirés
     */
    public function findUsersWithExpiredTokens(): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.token IS NOT NULL')
            ->andWhere('u.tokenExpiresAt < :now')
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    /**
     * Trouve un utilisateur par son identifiant unique (email)
     */
    public function findOneByUserIdentifier(string $userIdentifier): ?User
    {
        return $this->findByEmail($userIdentifier);
    }
}
