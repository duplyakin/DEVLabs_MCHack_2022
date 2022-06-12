<?php

namespace App\EventSubscriber;

use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Notification\NotificationNode;
use App\Event\CreateUserEvent;
use App\Event\NotificationEvent;
use App\Service\Notification\NotificationServiceInterface;
use App\Service\Notification\Transport\Email;
use App\Service\Notification\Transport\TelegramBot;
use App\Service\Notification\Transport\TelegramNative;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationSubscriber
 * @package App\EventSubscriber
 */
class NotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationServiceInterface
     */
    private $notificationService;

    /**
     * NotificationSubscriber constructor.
     * @param NotificationServiceInterface $notificationService
     */
    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            NotificationEvent::class => 'onNotificationEvent',
            CreateUserEvent::class => 'onCreateUser',
        ];
    }

    /**
     * @param NotificationEvent $event
     */
    public function onNotificationEvent(NotificationEvent $event)
    {
        $this->notificationService->handle($event);
    }

    /**
     * @param CreateUserEvent $event
     */
    public function onCreateUser(CreateUserEvent $event)
    {
        $collection = new ArrayCollection([$event->getUser()]);
        $event = (new NotificationEvent())
            ->setEventType(NotificationNode::EVENT_TYPE_CREATE_USER)
            ->setUsers($collection)
            ->setTransports([
                Email::class,
                ($event->getUser()->isUseTelegram() ? TelegramBot::class : TelegramNative::class),
            ])
            ->setCommunity($event->getCommunity());

        $this->notificationService->handle($event);
    }
}
