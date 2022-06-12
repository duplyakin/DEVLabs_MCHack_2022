<?php

namespace App\Repository;

use App\Entity\BalanceTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BalanceTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method BalanceTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method BalanceTransaction[]    findAll()
 * @method BalanceTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BalanceTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BalanceTransaction::class);
    }

    // /**
    //  * @return BalanceTransaction[] Returns an array of BalanceTransaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BalanceTransaction
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
