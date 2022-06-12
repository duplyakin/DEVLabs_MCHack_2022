<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Metric;

use App\Entity\Community;
use App\Entity\UserCommunitySetting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Predis\Client;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class SortService
 * @package App\Service\Calendar\Metric
 */
class SortService
{
    /**
     * @var array
     */
    private $stopWords = ['проект', 'продукт'];
    /**
     * @var array
     */
    private $connectedUsers = [];
    /**
     * @var array
     */
    private $unsortedUsers = [];
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Client
     */
    private $redis;

    /**
     * SortService constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param Client $redis
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        Client $redis
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->redis = $redis;
    }

    /**
     * @param array $users
     * @return array
     */
    public function getSortedUsers(array $users)
    {
        $this->unsortedUsers = [];
        set_time_limit(0);
        $this->filterIsNullableUser($users);
        $this->sort($users);
        $this->sortByAnswers($users);
        $this->sortByConnect($users);
        return array_values($users);
    }

    /**
     * @return array
     */
    public function getUnsortedUsers(): array
    {
        return $this->unsortedUsers;
    }

    /**
     * @param $users
     */
    protected function sortByConnect(&$users)
    {
        $num = count($users);
        $iterations = $num - 1;
        for ($i = 0; $i < $num; $i += 2) {
            if (isset($users[$i + 1]) && $this->isWrongConnect($users[$i], $users[$i + 1])) {
                for ($j = ($i + 2); $j < $iterations; $j++) {
                    if (!isset($users[$j])) {
                        continue;
                    }

                    if (
                        !$this->isWrongConnect($users[$i], $users[$j]) &&
                        $this->roundSocialGroup($users[$i]['rate']) == $this->roundSocialGroup($users[$j]['rate'])
                    ) {
                        list($users[($i + 1)], $users[$j]) = [
                            $users[$j], $users[($i + 1)],
                        ];
                    }
                }
            }
        }

        $num = count($users);
        $iterations = $num - 1;
        for ($i = 0; $i < $num; $i += 2) {
            if (isset($users[$i + 1]) && $this->isWrongConnect($users[$i], $users[$i + 1])) {
                for ($j = ($i + 2); $j < $iterations; $j++) {
                    if (!isset($users[$j])) {
                        continue;
                    }

                    if (
                        !$this->isWrongConnect($users[$i], $users[$j]) &&
                        abs($this->roundSocialGroup($users[$i]['rate']) - $this->roundSocialGroup($users[$j]['rate'])) <= 1
                    ) {
                        list($users[($i + 1)], $users[$j]) = [
                            $users[$j], $users[($i + 1)],
                        ];
                    }
                }
            }
        }

        $users = array_values($users);
        $num = count($users);
        for ($i = 0; $i < $num; $i += 2) {
            if (isset($users[$i + 1]) && $this->isWrongConnect($users[$i], $users[$i + 1])) {
                $this->unsortedUsers[$i] = $users[$i];
                $this->unsortedUsers[$i + 1] = $users[$i + 1];
            }
        }

        foreach ($this->unsortedUsers as $i => $unsortedUser) {
            if (isset($users[$i])) {
                unset($users[$i]);
            }
        }
    }

    /**
     * @param $users
     * @return array
     */
    protected function sortByAnswers(&$users)
    {
        $users = array_values($users);
        $num = count($users);
        $iterations = $num - 1;
        for ($i = 0; $i < $num; $i += 2) {
            if (!isset($users[$i])) {
                break;
            }

            $ownerRate = $this->roundSocialGroup($users[$i]['rate']);
            $max = 0;
            $index = -1;
            for ($j = ($i + 1); $j < $iterations; $j++) {
                if (!isset($users[$j])) {
                    continue;
                }
                $rate = $this->roundSocialGroup($users[$j]['rate']);
                if ($rate != $ownerRate) {
                    break;
                }
                $intersect = array_intersect(
                    (array)$users[$i]['user']->getDirectAnswerIds(),
                    (array)$users[$j]['user']->getDirectAnswerIds()
                );
                $current = count($intersect);
                if (
                    $current >= $max &&
                    $users[$i]['rank'] <= 0.3 &&
                    $users[$j]['rank'] <= 0.3 &&
                    (
                        !$this->isWrongConnect($users[$i], $users[$j]) &&
                        ($j % 2 != 0 && !$this->isWrongConnect($users[($i + 1)], $users[($j - 1)])) &&
                        ($j % 2 == 0 && !$this->isWrongConnect($users[($i + 1)], $users[($j + 1)]))
                    )
                ) {
                    $max = $current;
                    $index = $j;
                }
            }
            if ($index != -1 && $users[($i + 1)]['rank'] <= 0.3) {
                list($users[($i + 1)], $users[$index]) = [
                    $users[$index], $users[($i + 1)],
                ];
            }
            $iterations--;
        }

        // highlight
        for ($i = 0; $i < $num; $i += 2) {
            if (!isset($users[$i]) || !isset($users[($i + 1)])) {
                break;
            }
            $intersect = array_intersect(
                (array)$users[$i]['user']->getDirectAnswerIds(),
                (array)$users[($i + 1)]['user']->getDirectAnswerIds()
            );
            $users[$i]['user']->setIntersectAnswers($intersect);
            $users[($i + 1)]['user']->setIntersectAnswers($intersect);
        }
        return $users;
    }

    /**
     * @param $rate
     * @return int
     */
    protected function roundSocialGroup($rate)
    {
        return intval($rate);
    }


    /**
     * @param $prev
     * @param $next
     * @return bool
     */
    protected function isWrongConnect($prev, $next)
    {
        return ($this->isConnected($prev, $next) || $this->isInvited($prev, $next));
    }

    /**
     * @param $prev
     * @param $next
     * @return int
     */
    protected function isConnected($prev, $next)
    {
        if (!isset($this->connectedUsers[$prev['user']->getId()])) {
            return false;
        }
        $prev['user']->setUserConnects($this->connectedUsers[$prev['user']->getId()]);
        if (isset($this->connectedUsers[$next['user']->getId()])) {
            $next['user']->setUserConnects($this->connectedUsers[$next['user']->getId()]);
        }
        return in_array($next['user']->getId(), $this->connectedUsers[$prev['user']->getId()]);
    }

    /**
     * @param $prev
     * @param $next
     * @return bool
     */
    protected function isInvited($prev, $next)
    {
        return false;
//        return $prev['user']->getMyInvitedUsers() ?
//            $prev['user']->getMyInvitedUsers()->contains($next['user']) : false;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function sort(array &$data): array
    {
        $countElements = count($data);
        $iterations = $countElements - 2;
        for ($i = 0; $i < $countElements; $i += 2) {
            if (!isset($data[$i + 1])) {
                $this->unsortedUsers[$i] = $data[$i];
                break;
            }
            if (!empty($data[$i]['direct_order'])) {
                continue;
            }
//            if ($this->isWrongConnect($data[$i], $data[$i + 1])) {
//            $unsorted = true;
            $unsorted = false;
            $index = -1;
            $rank = 0;
            for ($j = ($i + 1); $j <= ($iterations + $i + 1); $j++) {
                if (!isset($data[$j])) {
                    break;
                }
                if ($data[$i + 1]['rate'] == $this->roundSocialGroup($data[$i]['rate']) && $this->roundSocialGroup($data[$i]['rate']) != $this->roundSocialGroup($data[$j]['rate'])) {
                    continue;
                }
                if ($this->isWrongConnect($data[$i], $data[$j])) {
                    continue;
                }
                $percent = 0;
                /** @var UserCommunitySetting $settings */
                if ($settings = $data[$i]['user']->getUserCommunitySettings()->first()) {
                    $redisKey = 'MARGO_SCORE_' . md5($data[$i]['user']->getAbout() . ' ' . $settings->getLookingFor()) . '.' . md5($data[$j]['user']->getAbout());
                    if ($persistRank = $this->redis->get($redisKey)) {
                        $percent = $persistRank;
                    } else {
                        $looks = explode(' ', trim(preg_replace('/[^0-9a-zа-я\s]/u', ' ', mb_strtolower($data[$i]['user']->getAbout() . ' ' . $settings->getLookingFor() . ' ' . $settings->getLookingFor()))));
                        foreach (explode(' ', trim(preg_replace('/[^0-9a-zа-я\s]/u', ' ', mb_strtolower($data[$j]['user']->getAbout())))) as $about) {
                            if (mb_strlen($about) <= 3) {
                                continue;
                            }
                            foreach ($looks as $look) {
                                if (mb_strlen($look) <= 3) {
                                    continue;
                                }
                                if (in_array($look, $this->stopWords)) {
                                    continue;
                                }
                                if (in_array($about, $this->stopWords)) {
                                    continue;
                                }
                                if (($score = levenshtein($about, $look)) < 3) {
                                    $percent += 1 / ++$score;
                                }
                            }
                        }
                        if ($percent > 0) {
                            $this->redis->setex($redisKey, 60 * 60 * 24 * 30, $percent);
                        }
                    }
                }
                $userRank = $data[$j]['rank'] + $percent / 4;
                if ($userRank == $rank && !$this->isWrongConnect($data[$i], $data[$j])) {
                    $rank = $userRank;
                    $index = $j;
                }
            }
            if ($index != -1) {
                $data[$index]['rank'] = $rank;
                list($data[$i + 1], $data[$index]) = [
                    $data[$index], $data[$i + 1]
                ];
                $unsorted = false;
            }
            if ($unsorted) {
                $this->unsortedUsers[$i] = $data[$i];
            }
//            }
            $iterations -= 2;
        }
        foreach ($this->unsortedUsers as $i => $unsortedUser) {
            unset($data[$i]);
        }
        return $data;
    }

    /**
     * @param $users
     */
    protected function filterIsNullableUser(&$users)
    {
        if (!$this->request) {
            return;
        }
        if ($this->request->get('showNullable') != 1) {
            return;
        }
        $users = array_filter($users, function ($user) {
            return empty($user['rate']);
        });
        $users = array_values($users);
    }

    /**
     * @param array $connectedUsers
     * @return SortService
     */
    public function setConnectedUsers(array $connectedUsers): SortService
    {
        foreach ($connectedUsers as $connectedUser) {
            $this->connectedUsers[$connectedUser['first_user_id']][] = $connectedUser['second_user_id'];
        }
        return $this;
    }

    /**
     * @param QueryBuilder $builder
     * @param Community $community
     * @return QueryBuilder
     */
    public function filter(QueryBuilder $builder, Community $community): QueryBuilder
    {
        if (!$this->request) {
            return $builder;
        }

        if ($this->request->get('showNullable') == 1) {
            return $builder;
        }

//        if ($this->request->get('readyToMatch') == 'on' || is_null($this->request->get('readyToMatch'))) {
//            $builder->andWhere('u.readyToMatch = 1');
//        }

        if ($community->getIsPaid() && !$this->request->get('nullBalance', false)) {
            $builder->innerJoin('u.userCommunityBalances', 'ub');
            $builder->innerJoin('ub.community', 'ubc');
            $builder->andWhere('ubc.id = c.id');
            $builder->andWhere('ub.value > 0');
        }

        return $builder;
    }

//    /**
//     * @return Community|null
//     */
//    public function getCommunity()
//    {
//        if (!$this->request) {
//            return null;
//        }
//
//        if (!$this->request->get('communityId')) {
//            return null;
//        }
//        /** @var CommunityRepository $communityRepository */
//        $communityRepository = $this->entityManager->getRepository(Community::class);
//        return $communityRepository->find($this->request->get('communityId'));
//    }

}