<?php

namespace App\Repository;

use App\Entity\ConnectNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConnectNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConnectNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConnectNote[]    findAll()
 * @method ConnectNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectNote::class);
    }

    // /**
    //  * @return ConnectNote[] Returns an array of ConnectNote objects
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
    public function findOneBySomeField($value): ?ConnectNote
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
