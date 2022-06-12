<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineExtensions\Query\Mysql\Now;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method CallUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallUser[]    findAll()
 * @method CallUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallUserRepository extends ServiceEntityRepository
{
    /**
     * CallUserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallUser::class);
    }


    /**
     * @param UserInterface $user
     * @param Call $call
     * @return CallUser|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRemoteUserCall(UserInterface $user, Call $call): ?CallUser
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.callInstance = :call')
            ->andWhere('c.user != :user')
            ->setParameter('call', $call)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $chatId
     * @return mixed
     */
    public function findUserByPartnerChatId(int $chatId)
    {
        return $this->createQueryBuilder('u')
            ->join('u.callInstance', 'c')
            ->join('c.users', 'cu', 'WITH', 'cu.telegramChatId = :chatId')
            ->andWhere('u.id != cu.id')
            ->andWhere('u.telegramChatId is not null')
            ->andWhere('week(CURRENT_DATE()) = week(c.created_at)')
//            ->setMaxResults(1)
            ->orderBy('c.created_at', 'desc')
            ->setParameter('chatId', $chatId)
            ->getQuery()
            ->getResult();
//            ->getOneOrNullResult();
    }

    /*
    public function findOneBySomeField($value): ?CallUser
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
