<?php

namespace App\Repository;

use App\Entity\PriorityMetricKeyword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriorityMetricKeyword|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriorityMetricKeyword|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriorityMetricKeyword[]    findAll()
 * @method PriorityMetricKeyword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriorityMetricKeywordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriorityMetricKeyword::class);
    }

    // /**
    //  * @return PriorityMetricKeyword[] Returns an array of PriorityMetricKeyword objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PriorityMetricKeyword
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
