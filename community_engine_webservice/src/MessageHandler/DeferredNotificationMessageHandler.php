<?php

namespace App\MessageHandler;

use App\Message\DeferredNotificationMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class SyncNotificationMessageHandler
 * @package App\MessageHandler
 */
final class DeferredNotificationMessageHandler implements MessageHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * SyncNotificationMessageHandler constructor.
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param DeferredNotificationMessage $message
     */
    public function __invoke(DeferredNotificationMessage $message)
    {
        $this->bus->dispatch($message->getMessage());
    }
}
