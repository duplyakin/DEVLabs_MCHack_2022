<?php

namespace App\MessageHandler;

use App\Message\TelegramBotSendMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

final class TelegramBotSendMessageHandler implements MessageHandlerInterface
{
    /**
     * @var BotApi
     */
    private $api;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TelegramBotSendMessageHandler constructor.
     * @param BotApi $api
     * @param LoggerInterface $logger
     */
    public function __construct(
        BotApi $api,
        LoggerInterface $logger
    )
    {
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * @param TelegramBotSendMessage $message
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function __invoke(TelegramBotSendMessage $message)
    {
        $this->logger->info('SEND', [
            'chatId' => $message->getChatId(),
            'text' => $message->getMessageText(),
            'keyboard' => $message->getKeyboardMarkup(),
        ]);

        try {
            $this->api->sendMessage(
                $message->getChatId(),
                $message->getMessageText(),
                'html',
                true,
                null,
                $message->getKeyboardMarkup()
            );
        } catch (Exception $e) {
            $this->logger->info('ERROR=' . $e->getMessage(), [
                'chatId' => $message->getChatId(),
                'text' => $message->getMessageText(),
                'keyboard' => $message->getKeyboardMarkup(),
            ]);
        }
    }
}
