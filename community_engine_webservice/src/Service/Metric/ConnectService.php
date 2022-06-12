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
use App\Entity\User;
use App\Message\ConnectByCommunityMessage;
use App\Repository\CommunityRepository;
use App\Repository\UserRepository;
use App\Service\Call\MakeCallService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class ConnectService
 * @package App\Service\Metric
 */
class ConnectService
{
    /**
     * @var MakeCallService
     */
    private $callService;
    /**
     * @var SortService
     */
    private $sortService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * ConnectService constructor.
     * @param MakeCallService $callService
     * @param SortService $sortService
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     */
    public function __construct(
        MakeCallService $callService,
        SortService $sortService,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    )
    {
        $this->callService = $callService;
        $this->sortService = $sortService;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        /** @var CommunityRepository $communityRepository */
        $communityRepository = $this->entityManager->getRepository(Community::class);
        /** @var Community[] $communities */
        $communities = $communityRepository->findAllOrderByPrivate();

        foreach ($communities as $community) {
            $createGroup = new ConnectByCommunityMessage($community);
            $this->bus->dispatch($createGroup);
        }

        //////
        // Non-community and no-matching users connect
        //////
//        $users = $usersQuery
//            ->andWhere('u.readyToMatch = 1')
//            ->getQuery()
//            ->getResult();
//        $users = $this->sortService
//            ->setConnectedUsers($connectedUsers)
//            ->getSortedUsers($users);
//
//        $this->connect($users);
        //////
    }
}