<?php

namespace App\Repository;

use App\Entity\UserMetricField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMetricField|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMetricField|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMetricField[]    findAll()
 * @method UserMetricField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMetricFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMetricField::class);
    }

    // /**
    //  * @return UserMetricField[] Returns an array of UserMetricField objects
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
    public function findOneBySomeField($value): ?UserMetricField
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
