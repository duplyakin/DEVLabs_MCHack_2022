<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Notification;


use App\Entity\Notification\Notification;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Repository\Notification\NotificationRepository;
use App\Service\Notification\Transport\NotificationTransportService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Predis\Client;

class NotificationService implements NotificationServiceInterface
{
    /**
     * @var NotificationTransportService
     */
    private $transportService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Client
     */
    private $redisClient;

    /**
     * NotificationService constructor.
     * @param NotificationTransportService $transportService
     * @param EntityManagerInterface $entityManager
     * @param Client $redisClient
     */
    public function __construct(
        NotificationTransportService $transportService,
        EntityManagerInterface $entityManager,
        Client $redisClient
    )
    {
        $this->transportService = $transportService;
        $this->entityManager = $entityManager;
        $this->redisClient = $redisClient;
    }

    /**
     * @param NotificationEvent $event
     * @return void
     */
    public function handle(NotificationEvent $event): void
    {
        try {
            $notificationEntity = $this->getNotificationEntity($event);
            if (!$notificationEntity) {
                // TODO LOGS
                return;
            }

            $users = $event->getUsers()->filter(function (User $user) use ($event) {
                if (is_null($event->getCommunity())) {
                    return true;
                }
                $setting = $user->getSettingsByCommunity($event->getCommunity());
                return $setting && $setting->getSendNotifications();
            });

            if ($users->isEmpty()) {
                //TODO logs
                return;
            }

            //TODO send to messenger bus deferred key and delete it form redis if notification sent
            if ($event->isDeferred()) {
                if ($this->redisClient->get($event->getDeferred())) {
                    return;
                }
                $this->redisClient->setex($event->getDeferred(), 180, true);
            }

            $this->transportService->handle(
                $notificationEntity,
                $users,
                $event->getMeta(),
                $event->getCommunity(),
                $event->isDeferred()
            );
        } catch (NonUniqueResultException $e) {
            //TODO logs
        }
    }

    /**
     * @param NotificationEvent $event
     * @return Notification|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getNotificationEntity(NotificationEvent $event): ?Notification
    {
        $this->entityManager->clear(Notification::class);

        /** @var NotificationRepository $repository */
        $repository = $this->entityManager->getRepository(Notification::class);
        $result = $repository->findWithTransport(
            $event->getEventType(),
            $event->getTransports(),
            $event->getCommunity()
        );

        if ($result instanceof Notification) {
            return $result;
        }

        return $repository->findWithTransport(
            $event->getEventType(),
            $event->getTransports(),
            null
        );
    }
}