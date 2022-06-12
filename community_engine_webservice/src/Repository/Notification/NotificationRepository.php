<?php

namespace App\Repository\Notification;

use App\Entity\Community;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationTransport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @param string $eventType
     * @param array $transports
     * @param Community|null $community
     * @return null|Notification
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findWithTransport(string $eventType, array $transports, ?Community $community): ?Notification
    {
        $qb = $this->createQueryBuilder('n')
            ->select(['n', 't'])
            ->innerJoin('n.notificationTransports', 't')
            ->andWhere('n.node = :type')
            ->andWhere('t.node IN (:transports)')
            ->setParameters([
                'type' => $eventType,
                'transports' => $transports,
            ]);

        if ($community instanceof Community) {
            $qb->andWhere('t.community = :community')
                ->setParameter('community', $community);

        } else {
            $qb->andWhere('t.community IS NULL');
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Notification[] Returns an array of Notification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
