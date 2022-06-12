<?php

namespace App\EventSubscriber;

use App\Service\TelegramBot\KeyboardHandlerService;
use App\Service\TelegramBot\TelegramMessageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use TelegramBot\Api\Types\MessageEntity;

class TelegramUpdateSubscriber implements EventSubscriberInterface
{
    /**
     * @var KeyboardHandlerService
     */
    private $keyboardHandlerService;
    /**
     * @var TelegramMessageService
     */
    private $telegramMessageService;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TelegramUpdateSubscriber constructor.
     * @param KeyboardHandlerService $keyboardHandlerService
     * @param TelegramMessageService $telegramMessageService
     * @param LoggerInterface $logger
     */
    public function __construct(
        KeyboardHandlerService $keyboardHandlerService,
        TelegramMessageService $telegramMessageService,
        LoggerInterface $logger
    )
    {
        $this->keyboardHandlerService = $keyboardHandlerService;
        $this->telegramMessageService = $telegramMessageService;
        $this->logger = $logger;
    }

    /**
     * @param UpdateEvent $event
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function onUpdateEvent(UpdateEvent $event)
    {
        $update = $event->getUpdate();

        $message = $update->getMessage() ? $update->getMessage() : $update->getEditedMessage();

        $this->logger->info('INCOMING', [
            'message' => ($message ? $message->getText() : null),
            'from' => ($message ? $message->getFrom()->getId() : null),
            'username' => ($message ? $message->getFrom()->getUsername() : null),
        ]);

        if ($this->keyboardHandlerService->handle($update->getCallbackQuery())) {
            return;
        }

        $entities = $message && $message->getEntities() ? $message->getEntities() : [];
        if (
            isset($entities[0]) &&
            $entities[0] instanceof MessageEntity &&
            $entities[0]->getType() == MessageEntity::TYPE_BOT_COMMAND
        ) {
            return;
        }

        $this->telegramMessageService->handle($update);
    }

    public static function getSubscribedEvents()
    {
        return [
            UpdateEvent::class => 'onUpdateEvent',
        ];
    }
}
