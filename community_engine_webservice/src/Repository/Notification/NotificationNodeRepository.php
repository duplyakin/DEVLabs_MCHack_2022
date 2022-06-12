<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationNode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotificationNode|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationNode|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationNode[]    findAll()
 * @method NotificationNode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationNodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationNode::class);
    }

    // /**
    //  * @return NotificationNode[] Returns an array of NotificationNode objects
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
    public function findOneBySomeField($value): ?NotificationNode
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
