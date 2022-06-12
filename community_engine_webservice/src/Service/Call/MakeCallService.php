<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Call;


use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\Community;
use App\Entity\MetricOrder;
use App\Entity\Notification\NotificationNode;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserCommunitySetting;
use App\Entity\UserMetricField;
use App\Event\CreateConnectEvent;
use App\Event\NotificationEvent;
use App\Message\CreateTelegramGroupMessage;
use App\Message\SendEmailMessage;
use App\Repository\CommunityRepository;
use App\Repository\UserMetricFieldRepository;
use App\Repository\UserRepository;
use App\Service\Notification\Transport\Email;
use App\Service\Notification\Transport\TelegramBot;
use App\Service\Notification\Transport\TelegramNative;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class MakeCallService
 * @package App\Service\Call
 */
class MakeCallService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    protected $switchReadyToMatch = true;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var ProfileService
     */
    private $profileService;
    /**
     * @var CommunityRepository
     */
    private $communityRepository;

    /**
     * MakeCallService constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $registry
     * @param ProfileService $profileService
     * @param CommunityRepository $communityRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $registry,
        ProfileService $profileService,
        CommunityRepository $communityRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->registry = $registry;
        $this->profileService = $profileService;
        $this->communityRepository = $communityRepository;
    }

    /**
     * @param array $ids
     * @param Community $community
     * @return bool
     * @throws \Throwable
     */
    public function createFromIds(array $ids, Community $community)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findBy([
            'id' => $ids,
        ]);
        if (!empty($users)) {
            return $this->create($users, $community);
        }
        return false;
    }

    /**
     * @param User[] $users
     * @param Community|null $community
     * @return mixed
     * @throws \Throwable
     */
    public function create(array $users, ?Community $community = null)
    {

        $this->entityManager->beginTransaction();
        try {
            //TODO unit of work doctrine
            if ($community) {
                $community = $this->communityRepository->find($community->getId());
            }

            $call = new Call();
            $call->setCommunity($community);
            $this->entityManager->persist($call);

            foreach ($users as $user) {
                $this->addUserToCall($call, $user);
            }

            if (
                $community &&
                isset($users[0], $users[1]) &&
                $users[0] instanceof User &&
                $users[1] instanceof User
            ) {

                if ($this->isSwitchReadyToMatch()) {
                    $this->entityManager->createQueryBuilder()
                        ->update(UserCommunitySetting::class, 'us')
                        ->set('us.ready', 'false')
                        ->andWhere('us.community = :community')
                        ->andWhere('us.user IN (:users)')
                        ->setParameters([
                            'users' => $users,
                            'community' => $community,
                        ])
                        ->getQuery()
                        ->execute();
                }

                $this->entityManager->createQueryBuilder()
                    ->delete(MetricOrder::class, 'm')
                    ->andWhere('m.user = :user')
                    ->andWhere('m.withUser = :withUser')
                    ->andWhere('m.community = :community')
                    ->setParameters([
                        'user' => $users[0],
                        'withUser' => $users[1],
                        'community' => $community,
                    ])
                    ->getQuery()
                    ->execute();
            }

            $event = (new NotificationEvent())
                ->setTransports([
                    Email::class,
                    TelegramBot::class,
                    TelegramNative::class,
                ])
                ->setUsers(new ArrayCollection($users))
                ->setCommunity($community)
                ->setEventType(NotificationNode::EVENT_TYPE_CONNECT)
                ->setMeta([
                    NotificationEvent::IS_CONNECT_EVENT_KEY => true,
                    NotificationEvent::CONNECT_OBJECT_KEY => $call,
                ]);

            $this->eventDispatcher->dispatch($event);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            if (!$this->entityManager->isOpen()) {
                $this->registry->resetManager();
            }
            $this->logger->info('ERROR CREATE CONNECT', [
                'users' => $users,
                'community' => $community ? $community->getId() . '|' . $community->getTitle() : null,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        $this->logger->info('Connect create', [
            'connect_id' => $call->getId(),
            'users' => $users,
            'community' => $community ? $community->getId() . '|' . $community->getTitle() : null,
        ]);

        return $call;
    }

    /**
     * @param Call $call
     * @param UserInterface $user
     */
    public function addUserToCall(Call $call, UserInterface $user)
    {
        /** @var $user User */
        $callUser = new CallUser();
        $callUser->setTelegramChatId($user->getTelegramId());
        $callUser->setUser($user);
        $call->addUser($callUser);
        $this->entityManager->persist($callUser);
        $this->entityManager->persist($user);
    }

    /**
     * @return bool
     */
    public function isSwitchReadyToMatch(): bool
    {
        return $this->switchReadyToMatch;
    }

    /**
     * @param bool $switchReadyToMatch
     * @return MakeCallService
     */
    public function setSwitchReadyToMatch(bool $switchReadyToMatch): self
    {
        $this->switchReadyToMatch = $switchReadyToMatch;
        return $this;
    }
}