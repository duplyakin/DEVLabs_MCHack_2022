<?php

namespace App\Repository;

use App\Entity\Community;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function findAllByCommunityGroupByCreatedAt(Community $community)
    {
        return $this->createQueryBuilder('r')
            ->select([
                'min(r.created_at) as created',
                'week(r.created_at) as week',
                'year(r.created_at) as year',
                'avg(r.rate) as avg',
            ])
            ->innerJoin('r.user', 'u')
            ->innerJoin('r.rate_to', 'u2')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere(':community MEMBER OF u2.communities')
            ->andWhere('r.is_successful = true')
            ->setParameter(':community', $community)
            ->groupBy('year, week')
            ->orderBy('year, week', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    // /**
    //  * @return Review[] Returns an array of Review objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
