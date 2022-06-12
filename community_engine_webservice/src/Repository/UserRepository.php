<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\CallUser;
use App\Entity\Community;
use App\Entity\MetricOrder;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserCommunitySetting;
use App\Entity\UserMetric;
use App\Entity\UserMetricField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineExtensions\Query\SortableNullsWalker;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param $params
     * @return User[]
     */
    public function checkEmail($params)
    {
        return $this->findBy(['email' => $params['emailAlt']]);
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function findAllWithQuestionByCommunity(Community $community)
    {
        return $this->createQueryBuilder('u')
            ->select(['u', 'q', 'a'])
            ->leftJoin('u.answers', 'a')
            ->leftJoin('a.question', 'q')
            ->leftJoin('u.callItem', 'ci')
            ->innerJoin('u.communities', 'c')
            ->leftJoin('u.userCommunitySettings', 's', 'WITH', 's.community = c')
            ->leftJoin('ci.callInstance', 'call')
            ->andWhere('u.hold = false')
            ->andWhere('s.ready = true')
            ->andWhere('s.send_notifications = true')
            ->andWhere('s.community = :community')
            ->andWhere(':community MEMBER OF u.communities')
            ->setParameter(':community', $community)
            ->orderBy('call.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Community $community
     * @return Query
     */
    public function getUserByCommunityQuery(Community $community)
    {
        return $this->createQueryBuilder('u')
            ->select(['u', 'c', 's'])
            ->leftJoin('u.userCommunitySettings', 's', 'WITH', 's.community = :community')
            ->leftJoin('u.communities', 'c')
            ->andWhere(':community MEMBER OF u.communities')
            ->setParameter('community', $community)
            ->orderBy('u.id', 'DESC')
            ->getQuery();
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function findAllByCommunityGroupByCreatedAt(Community $community)
    {
        return $this->createQueryBuilder('u')
            ->select([
                'date(u.created_at) as created',
                'count(u.id) as count',
            ])
            ->groupBy('created')
            ->andWhere(':community MEMBER OF u.communities')
            ->setParameter(':community', $community)
            ->orderBy('created', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param Community $community
     * @return array
     */
    public function findAllForMetricQueryBuilder(Community $community): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(User::class, 'u', 'user')
            ->addFieldResult('u', 'u_id', 'id')
            ->addFieldResult('u', 'u_about', 'about')
            ->addFieldResult('u', 'u_first_name', 'firstName')
            ->addFieldResult('u', 'u_last_name', 'lastName')
            ->addFieldResult('u', 'u_email', 'email')
            ->addFieldResult('u', 'u_email_alt', 'emailAlt')
            ->addFieldResult('u', 'u_telegram_username', 'telegramUsername')
            ->addFieldResult('u', 'u_telegram_id', 'telegramId')
            ->addFieldResult('u', 'u_picture', 'picture')
            ->addFieldResult('u', 'u_facebook_link', 'facebookLink')
            ->addFieldResult('u', 'u_linkedin_link', 'linkedinLink')
            //
            ->addJoinedEntityResult(UserCommunitySetting::class, 'ucs', 'u', 'userCommunitySettings')
            ->addFieldResult('ucs', 'looking_for', 'looking_for')
            ->addFieldResult('ucs', 'ucs_id', 'id')
            //
            ->addJoinedEntityResult(Community::class, 'c', 'ucs', 'community')
            ->addFieldResult('c', 'c_id', 'id')
            //
            ->addJoinedEntityResult(Answer::class, 'a', 'u', 'answers')
            ->addFieldResult('a', 'answer_title', 'title')
            ->addFieldResult('a', 'a_id', 'id')
            //
            ->addJoinedEntityResult(Question::class, 'q', 'a', 'question')
            ->addFieldResult('q', 'q_tag', 'tag')
            ->addFieldResult('q', 'q_title', 'title')
            ->addFieldResult('q', 'q_id', 'id')
            //
            ->addJoinedEntityResult(Answer::class, 'ra', 'a', 'relatedAnswer')
            ->addFieldResult('ra', 'ranswer_title', 'title')
            ->addFieldResult('ra', 'ra_id', 'id')
            //
            ->addJoinedEntityResult(User::class, 'u2', 'u', 'invitedBy')
            ->addFieldResult('u2', 'ui_id', 'id')
            //
            ->addScalarResult('rank', 'rank')
            ->addScalarResult('rate', 'rate');

        return $this->_em->createNativeQuery(
            <<<SQL
        select 
          u.id as u_id, 
          u.about as u_about,
          u.first_name as u_first_name,
          u.last_name as u_last_name,
          u.email as u_email,
          u.email_alt as u_email_alt,
          u.telegram_username as u_telegram_username,
          u.telegram_id as u_telegram_id, 
          u.picture as u_picture,
          u.facebook_link as u_facebook_link,
          u.linkedin_link as u_linkedin_link,
          ucs.id as ucs_id,
          ucs.looking_for,
          a.id as a_id,
          a.title as answer_title,
          u.invited_by_id as ui_id,
          ra.id as ra_id,
          ra.title as ranswer_title,
          ucs.community_id as c_id, 
          q.title as q_title, 
          q.tag as q_tag, 
          q.id as q_id, 
          ts_rank(to_tsvector('russian', u.about), to_tsquery('russian', (
            SELECT array_to_string(array(SELECT DISTINCT keyword FROM priority_metric_keyword), ' & ')
          ))) as rank,
          (
            select 
              SUM(CASE WHEN umf.type = :TYPE_BOOLEAN THEN (um.value * umf.multiplier) ELSE um.value END)
            from user_metric um
            left join user_metric_field umf on um.field_id = umf.id
            where um.user_id = u.id
          ) as rate
        from "user" u
        left join user_community_setting ucs on ucs.user_id = u.id
        left join user_answer ua on u.id = ua.user_id
        left join answer a on ua.answer_id = a.id
        left join question q on q.id = a.question_id
        left join answer ra on a.id = ra.related_answer_id
        where ucs.community_id = :communityId
              and ucs.question_complete = true 
              and ucs.send_notifications = true 
              and ucs.ready = true 
              and u.profile_complete = true
              and not exists (select 1 from metric_order mo where mo.user_id = u.id or mo.with_user_id = u.id)
        order by rate desc nulls last, rank desc nulls last;
SQL
            , $rsm)
            ->setParameter('TYPE_BOOLEAN', UserMetricField::TYPE_BOOLEAN)
            ->setParameter('communityId', $community->getId())
            ->getResult();
    }

    /**
     * @return array
     */
    public function findAllConnected()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'first_user_id');
        $rsm->addScalarResult('user2', 'second_user_id');

        //TODO T$T@#^&$T@&^#$T&$&@#^%*@#%
        $e = $this->_em->getConnection()->getDriver() instanceof Driver ? '"' : '`';

        return $this->_em->createNativeQuery('
            select distinct cu.user_id, cu2.user_id as user2 from call_user cu
            left join ' . $e . 'call' . $e . ' c2 on cu.call_instance_id = c2.id
            left join call_user cu2 on c2.id = cu2.call_instance_id and cu2.id != cu.id
        ', $rsm)
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param Answer $answer
     * @return mixed
     */
    public function findAllFilteredUsers(User $user, Answer $answer)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.hold = false')
            ->andWhere(':answer MEMBER OF u.answers')
            ->andWhere('u.id != :userId')
            ->orderBy('u.id', 'ASC')
            ->andWhere('u.readyToMatch = true')
            ->setParameter('userId', $user->getId())
            ->setParameter('answer', $answer)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findUsersForNotify()
    {
        return $this->createQueryBuilder('u')
            ->join('u.callItem', 'ci')
            ->join('ci.callInstance', 'call')
            ->andWhere('u.doNotDisturb is null')
            ->andWhere('u.readyToMatch = false')
            ->andWhere('u.questionComplete = true')
            ->andWhere('(WEEK(CURRENT_DATE()) - WEEK(call.callDate)) <= 3')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findUsersForNotifyReady()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.doNotDisturb is null')
            ->andWhere('u.readyToMatch = true')
            ->andWhere('u.questionComplete = true')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findUsersForNotifyUnReadyWithCalls()
    {
        return $this->createQueryBuilder('u')
            //  ->innerJoin('u.callItem', 'ci')
            //  ->innerJoin('ci.callInstance', 'call')
            ->andWhere('u.doNotDisturb is null')
            ->andWhere('u.readyToMatch = false')
            ->andWhere('u.questionComplete = true')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @param User $user
//     * @return mixed
//     */
//    public function findAllIdsMatchedUsersByUser(User $user)
//    {
//        return $this->createQueryBuilder('c', 'c.id')
//            ->select(['c', 'u'])
//            ->leftJoin('c.callItem', 'ci')
//            ->leftJoin('ci.user', 'u')
//            ->andWhere('c.id = :userId')
////            ->andWhere(':user MEMBER OF u.user')
//            ->setParameter('userId', $user->getId())
//            ->getQuery()
//            ->execute();
////            ->getResult(Query::HYDRATE_ARRAY);
//    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
