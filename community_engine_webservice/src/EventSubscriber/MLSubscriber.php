<?php

namespace App\EventSubscriber;

use App\Event\CreateUserEvent;
use App\Message\MLSentimentMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class MLSubscriber
 * @package App\EventSubscriber
 */
class MLSubscriber implements EventSubscriberInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * MLSubscriber constructor.
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CreateUserEvent::class => 'onCreateUser',
        ];
    }

    /**
     * @param CreateUserEvent $event
     */
    public function onCreateUser(CreateUserEvent $event)
    {
        $asyncEvent = new MLSentimentMessage();
        
        $asyncEvent->setUserId($event->getUser()->getId());
        $asyncEvent->setText($event->getUser()->getAbout());

        $this->bus->dispatch($asyncEvent);
    }
}
