<?php

namespace App\Repository;

use App\Entity\UserCommunityBalance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCommunityBalance|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCommunityBalance|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCommunityBalance[]    findAll()
 * @method UserCommunityBalance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommunityBalanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCommunityBalance::class);
    }

    // /**
    //  * @return UserCommunityBalance[] Returns an array of UserCommunityBalance objects
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
    public function findOneBySomeField($value): ?UserCommunityBalance
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
