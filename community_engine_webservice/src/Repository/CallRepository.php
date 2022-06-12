<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\Community;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Call|null find($id, $lockMode = null, $lockVersion = null)
 * @method Call|null findOneBy(array $criteria, array $orderBy = null)
 * @method Call[]    findAll()
 * @method Call[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    /**
     * @param UserInterface $user
     * @return \Doctrine\ORM\Query
     */
    public function findByUserQuery(UserInterface $user)
    {
        return $this->createQueryBuilder('c')
            ->select(['c', 'uc2', 'u', 'n'])
            ->leftJoin('c.users', 'uc')
            ->leftJoin('c.users', 'uc2')
            ->leftJoin('uc2.user', 'u')
            ->leftJoin('c.connectNotes', 'n', 'WITH', 'n.user = :user')
            ->andWhere('uc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery();
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function findAllByCommunityGroupByCreatedAt(Community $community)
    {
        return $this->createQueryBuilder('c')
            ->select([
                'min(c.created_at) as created',
                'week(c.created_at) as week',
                'year(c.created_at) as year',
                'count(distinct c.id) as count',
            ])
            ->leftJoin('c.users', 'uc')
            ->leftJoin('c.users', 'uc2', 'WITH', 'uc2.user != uc.user')
            ->leftJoin('uc.user', 'u')
            ->leftJoin('uc2.user', 'u2')
            // remove bad data
            ->andWhere('c.created_at > \'2020-08-15\'')
            ->andWhere('date(c.created_at) != \'2021-04-12\'')
            ->andWhere('date(c.created_at) != \'2021-04-13\'')
            ->andWhere('date(c.created_at) != \'2021-04-14\'')
            /////////
            ->andWhere('u.id != 1')
            ->andWhere('c.community is null OR c.community = :community')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere(':community MEMBER OF u2.communities')
            ->setParameter(':community', $community)
            ->groupBy('year, week')
            ->orderBy('year, week', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    public function findAllRange($from, $to)
    {
        return $this->createQueryBuilder('c')
            ->where('c.id >= :from and c.id <= :to')
            ->setParameters([
                'from' => $from,
                'to' => $to,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param bool $isReviewed
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneLastCallByUser(User $user, bool $isReviewed = false)
    {
        $sub = $this->getEntityManager()->createQueryBuilder();
        $sub->select("r.id");
        $sub->from(Review::class, "r");
        $sub->join('r.connect', 'rc');
        $sub->andWhere('r.user = :user');
        $sub->andWhere('rc.id = c.id');

        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.users', 'cu')
            ->andWhere(':user = cu.user')
            ->setParameter('user', $user)
            ->orderBy('c.created_at', 'DESC');

        if ($isReviewed) {
            $query->andWhere(
                $query->expr()->not(
                    $query->expr()->exists($sub->getDQL())
                )
            );
        }

        return $query
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Call[] Returns an array of Call objects
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
    public function findOneBySomeField($value): ?Call
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
