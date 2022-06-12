<?php

namespace App\Repository;

use App\Entity\UserMetric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMetric|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMetric|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMetric[]    findAll()
 * @method UserMetric[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMetricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMetric::class);
    }

    // /**
    //  * @return UserMetric[] Returns an array of UserMetric objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserMetric
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
