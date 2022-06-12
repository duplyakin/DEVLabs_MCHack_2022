<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationTransportNode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotificationTransportNode|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationTransportNode|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationTransportNode[]    findAll()
 * @method NotificationTransportNode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationTransportNodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationTransportNode::class);
    }

    // /**
    //  * @return NotificationTransportNode[] Returns an array of NotificationTransportNode objects
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
    public function findOneBySomeField($value): ?NotificationTransportNode
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
