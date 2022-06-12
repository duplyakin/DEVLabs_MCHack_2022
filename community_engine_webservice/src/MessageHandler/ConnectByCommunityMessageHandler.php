<?php

namespace App\MessageHandler;

use App\Entity\MetricOrder;
use App\Entity\User;
use App\Exception\Balance\BalanceHandlerException;
use App\Message\ConnectByCommunityMessage;
use App\Repository\MetricOrderRepository;
use App\Repository\UserRepository;
use App\Service\Balance\BalanceHandlerService;
use App\Service\Call\MakeCallService;
use App\Service\Metric\SortService;
use App\Service\Payment\Spend\Connect;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ConnectByCommunityMessageHandler implements MessageHandlerInterface
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
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var BalanceHandlerService
     */
    private $balanceHandlerService;
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConnectByCommunityMessageHandler constructor.
     * @param MakeCallService $callService
     * @param SortService $sortService
     * @param EntityManagerInterface $entityManager
     * @param BalanceHandlerService $balanceHandlerService
     * @param ManagerRegistry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        MakeCallService $callService,
        SortService $sortService,
        EntityManagerInterface $entityManager,
        BalanceHandlerService $balanceHandlerService,
        ManagerRegistry $registry,
        LoggerInterface $logger
    )
    {
        $this->callService = $callService;
        $this->sortService = $sortService;
        $this->entityManager = $entityManager;
        $this->balanceHandlerService = $balanceHandlerService;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param ConnectByCommunityMessage $message
     * @throws \Exception
     * @throws \Throwable
     */
    public function __invoke(ConnectByCommunityMessage $message)
    {
        /** @var MetricOrderRepository $metricOrderRepository */
        $metricOrderRepository = $this->entityManager->getRepository(MetricOrder::class);
        /** @var MetricOrder[] $orders */
        $orders = $metricOrderRepository->findOrderedUsers($message->getCommunity());

        foreach ($orders as $order) {
            $this->connect([
                ['user' => $order->getUser()],
                ['user' => $order->getWithUser()],
            ], $message);
            $this->logger->info('Connect success with order', [
                'id' => $order->getId(),
                'user' => $order->getUser()->getId(),
                'with_user' => $order->getWithUser()->getId(),
                'community_id' => $message->getCommunity()->getId(),
                'community_title' => $message->getCommunity()->getTitle(),
            ]);
        }

        $users = $this->sortService
            ->setConnectedUsers($this->getConnectedUsers())
            ->getSortedUsers($this->getUsers($message));
        $this->connect($users, $message);
    }

    /**
     * @param $users
     * @param ConnectByCommunityMessage $message
     * @throws \Exception
     * @throws \Throwable
     */
    protected function connect($users, ConnectByCommunityMessage $message)
    {
        for ($i = 0; $i < count($users); $i += 2) {
            if (!isset($users[($i + 1)])) {
                break;
            }
            $call = $this->callService
                ->setSwitchReadyToMatch(true)
                ->create([
                    $users[$i]['user'],
                    $users[$i + 1]['user'],
                ], $message->getCommunity());
            if ($call && $message->getCommunity()->getIsPaid()) {
                try {
                    $this->balanceHandlerService->handle(new Connect($call, $users[$i]['user'], $message->getCommunity()));
                    $this->balanceHandlerService->handle(new Connect($call, $users[$i + 1]['user'], $message->getCommunity()));
                } catch (BalanceHandlerException $e) {
                    $this->logger->error('Error handle balance', [
                        'message' => $e->getMessage(),
                        'user1' => $users[$i]['user'],
                        'user2' => $users[$i + 1]['user'],
                    ]);
                }
            }
        }
    }

    /**
     * @param ConnectByCommunityMessage $message
     * @return mixed
     */
    protected function getUsers(ConnectByCommunityMessage $message)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        return $userRepository->findAllForMetricQueryBuilder($message->getCommunity());

//        if ($message->getCommunity()->getIsPaid()) {
//            $query->innerJoin('u.userCommunityBalances', 'ub')
//                ->innerJoin('ub.community', 'ubc')
//                ->andWhere('ubc.id = c.id')
//                ->andWhere('ub.value > 0');
//        }
//
//        return $query
//            ->getQuery()
//            ->getResult();

    }

    /**
     * @return array
     */
    protected function getConnectedUsers()
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        return $userRepository->findAllConnected();
    }
}
