<?php

namespace App\Repository;

use App\Entity\Community;
use App\Entity\MetricOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetricOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetricOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetricOrder[]    findAll()
 * @method MetricOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetricOrderRepository extends ServiceEntityRepository
{
    /**
     * MetricOrderRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetricOrder::class);
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function findOrderedUsers(Community $community)
    {
        return $this->createQueryBuilder('m')
            ->select(['m', 'u', 'wu'])
            ->leftJoin('m.user', 'u')
            ->leftJoin('m.withUser', 'wu')
            ->leftJoin('m.community', 'c')
//            ->leftJoin('u.userCommunitySettings', 's1', 'WITH', 's1.community = c')
//            ->andWhere('u.hold = false')
//            ->andWhere('u.profile_complete = false')
//            ->andWhere('s1.ready = true')
//            ->andWhere('s1.send_notifications = true')
//            ->andWhere('s1.community = :community')
//            ->leftJoin('wu.userCommunitySettings', 's2', 'WITH', 's2.community = c')
//            ->andWhere('wu.hold = false')
//            ->andWhere('wu.profile_complete = false')
//            ->andWhere('s2.ready = true')
//            ->andWhere('s2.send_notifications = true')
//            ->andWhere('s2.community = :community')
            ->andWhere('m.community = :community')
//            ->andWhere(':community MEMBER OF u.communities')
//            ->andWhere(':community MEMBER OF wu.communities')
            ->setParameter(':community', $community)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return MetricOrder[] Returns an array of MetricOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MetricOrder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
