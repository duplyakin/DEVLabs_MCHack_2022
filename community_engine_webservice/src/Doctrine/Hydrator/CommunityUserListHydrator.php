<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\Hydrator;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator;

/**
 * Class CommunityUserListHydrator
 * @package App\Doctrine\Hydrator
 */
class CommunityUserListHydrator extends ObjectHydrator
{
    /**
     *
     */
    const USER_ID_FIELD = 'userForReview';

    /**
     * @var array
     */
    private $userIds = [];
    /**
     * @var array
     */
    private $userObjects = [];

    /**
     * @return array|mixed
     */
    protected function getUsersObjects()
    {
        if (!empty($this->userObjects)) {
            return $this->userObjects;
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->_em->getRepository(User::class);
        return $this->userObjects = $userRepository->createQueryBuilder('u', 'u.id')
            ->andWhere('u IN (:ids)')
            ->setParameter('ids', array_keys($this->userIds))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $userId
     * @return mixed|null
     */
    protected function getUserObjectById(int $userId)
    {
        return $this->getUsersObjects()[$userId] ?? null;
    }

    /**
     * @param array $result
     */
    protected function hydrateUsers(array &$result)
    {
        foreach ($result as &$item) {
            $item[self::USER_ID_FIELD] = $this->getUserObjectById((int)$item[self::USER_ID_FIELD]);
        }
    }

    /**
     * @return array
     */
    protected function hydrateAllData()
    {
        $result = parent::hydrateAllData();
        $this->hydrateUsers($result);
        return $result;
    }

    /**
     * @param array $row
     * @param array $result
     */
    protected function hydrateRowData(array $row, array &$result)
    {
        $field = key($this->_rsm->scalarMappings);
        if ($field && $this->_rsm->scalarMappings[$field] == self::USER_ID_FIELD && isset($row[$field])) {
            $this->userIds[$row[$field]] = 1;
        }
        parent::hydrateRowData($row, $result);
    }
}