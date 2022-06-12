<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationTransport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotificationTransport|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationTransport|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationTransport[]    findAll()
 * @method NotificationTransport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationTransportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationTransport::class);
    }

    // /**
    //  * @return NotificationTransport[] Returns an array of NotificationTransport objects
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
    public function findOneBySomeField($value): ?NotificationTransport
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
