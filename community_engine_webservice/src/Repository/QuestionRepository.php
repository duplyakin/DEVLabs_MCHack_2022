<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Community;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @param Community $community
     * @param int|null $type
     * @param array $tag
     * @param User|null $user
     * @return mixed
     */
    public function findAllByAnswerType(
        Community $community,
        ?int $type = Answer::TYPE_PUBLIC,
        ?array $tag = [],
        ?User $user = null
    )
    {
        $query = $this->createQueryBuilder('q')
            ->select(['q', 'a'])
            ->leftJoin('q.answers', 'a')
            ->leftJoin('q.answers', 'ap')
            ->andWhere(':community MEMBER OF q.communities')
            ->setParameter('community', $community);

        if (!empty($tag)) {
            $query->andWhere('q.tag IN (:tag)')
                ->setParameter('tag', $tag);
        }

        if ($user) {
            $query->andWhere(':user MEMBER OF a.users and a.type = :type_private')
                ->orWhere('a.type = :type_public')
                ->setParameter('user', $user)
                ->setParameter('type_private', Answer::TYPE_PRIVATE)
                ->setParameter('type_public', Answer::TYPE_PUBLIC);
        } elseif ($type) {
            $query->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }
//        dd($query->getQuery()->getSQL());
        return $query->getQuery()
            ->getResult();
    }

    public function findAllByQuestionTags(array $tags, ?User $user = null)
    {
        $query = $this->createQueryBuilder('q')
            ->andWhere('q.tag IN (:tags)')
            ->setParameter('tags', $tags);
        if ($user) {
            $query->join('q.answers', 'a')
                ->andWhere(':user MEMBER OF a.users')
                ->setParameter('user', $user);
        }
        return $query->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
