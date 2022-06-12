<?php

namespace App\Repository;

use App\Doctrine\Hydrator\CommunityUserListHydrator;
use App\Entity\Call;
use App\Entity\Community;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Community|null find($id, $lockMode = null, $lockVersion = null)
 * @method Community|null findOneBy(array $criteria, array $orderBy = null)
 * @method Community[]    findAll()
 * @method Community[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Community::class);
    }

    /**
     * @return Community[]
     */
    public function findAllOrderByPrivate()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.isPrivate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function findByUserWithCommunitySetting(User $user)
    {
        $sub = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, "user")
            ->innerJoin('user.callItem', 'ci')
            ->innerJoin('ci.callInstance', 'call')
            ->innerjoin('call.users', 'cu')
            ->leftjoin('call.reviews', 'r', 'WITH', 'r.user = cu.user')
            ->andWhere('cu.user = :user')
            ->andWhere('r is null')
            ->andWhere('user != :user')
            ->andWhere('call.community = c')
            ->orderBy('call.created_at', 'desc');

        $subCall = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('scall.uuid')
            ->from(Call::class, 'scall')
            ->innerjoin('scall.users', 'scu')
            ->leftjoin('scall.reviews', 'sr', 'WITH', 'sr.user = cs.user')
            ->andWhere('scu.user = :user')
            ->andWhere('sr is null')
            ->andWhere('scall.community = c')
            ->orderBy('scall.created_at', 'desc');

        return $this->createQueryBuilder('c')
            ->select('c as community', 'cs')
            // FIRST() -> See App\Doctrine\DBAL\FirstFunction
            ->addSelect('FIRST(' . $sub->getDQL() . ') as ' . CommunityUserListHydrator::USER_ID_FIELD)
            ->addSelect('FIRST(' . $subCall->getDQL() . ') as callUuid')
//            ->innerJoin('c.users', 'u')
            ->leftJoin('c.userCommunitySettings', 'cs', 'WITH', ':user = cs.user')
            ->andWhere(':user MEMBER OF c.users')
            ->setParameter('user', $user)
            ->getQuery()
            // CommunityUserListHydrator mode -> See App\Doctrine\Hydrator\CommunityUserListHydrator
            ->getResult('CommunityUserListHydrator');

    }

    /**
     * @param bool $ready
     * @return mixed
     */
    public function findAllWithUsersByReady(bool $ready)
    {
        $query = $this->createQueryBuilder('c')
            ->select(['c', 'u', 's'])
            ->leftJoin('c.users', 'u')
            ->leftJoin('u.userCommunitySettings', 's')
            ->andWhere('s.send_notifications = true')
            ->andWhere('u.profile_complete = true')
            ->andWhere('s.ready = :ready')
            ->setParameter('ready', $ready);

        return $query->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Community
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
