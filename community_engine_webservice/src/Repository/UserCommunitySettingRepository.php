<?php

namespace App\Repository;

use App\Entity\UserCommunitySetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCommunitySetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCommunitySetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCommunitySetting[]    findAll()
 * @method UserCommunitySetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommunitySettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCommunitySetting::class);
    }

    // /**
    //  * @return UserCommunitySetting[] Returns an array of UserCommunitySetting objects
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
    public function findOneBySomeField($value): ?UserCommunitySetting
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
