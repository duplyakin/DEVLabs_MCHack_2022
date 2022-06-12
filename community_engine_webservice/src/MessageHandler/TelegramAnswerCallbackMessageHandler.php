<?php

namespace App\MessageHandler;

use App\Message\TelegramAnswerCallbackMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use TelegramBot\Api\BotApi;

/**
 * Class TelegramAnswerCallbackMessageHandler
 * @package App\MessageHandler
 */
final class TelegramAnswerCallbackMessageHandler implements MessageHandlerInterface
{
    /**
     * @var BotApi
     */
    private $api;

    /**
     * TelegramBotSendMessageHandler constructor.
     * @param BotApi $api
     */
    public function __construct(BotApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param TelegramAnswerCallbackMessage $message
     */
    public function __invoke(TelegramAnswerCallbackMessage $message)
    {
        $this->api->answerCallbackQuery(
            $message->getCallbackId(),
            $message->getText(),
            $message->getShowAlert()
        );
    }
}
