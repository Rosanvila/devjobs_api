<?php

namespace App\Repository;

use App\Entity\Jobs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Jobs>
 */
class JobsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jobs::class);
    }

    //    /**
    //     * @return Jobs[] Returns an array of Jobs objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Jobs
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
