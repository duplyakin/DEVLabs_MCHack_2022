<?php

namespace App\Repository;

use App\Entity\CallStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CallStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallStep[]    findAll()
 * @method CallStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallStep::class);
    }

    // /**
    //  * @return CallStep[] Returns an array of CallStep objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CallStep
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
