<?php

namespace App\MessageHandler;

use App\Message\SendTelegramMessage;
use danog\MadelineProto\API;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SendTelegramMessageHandler
 * @package App\MessageHandler
 */
final class SendTelegramMessageHandler implements MessageHandlerInterface
{
    /**
     * @var \danog\MadelineProto\API
     */
    protected $madelineProto;

    /**
     * SendTelegramMessageHandler constructor.
     * @param API $madelineProto
     */
    public function __construct(API $madelineProto)
    {
        $this->madelineProto = $madelineProto;
        $this->madelineProto->start();
    }

    /**
     * @param SendTelegramMessage $telegram
     */
    public function __invoke(SendTelegramMessage $telegram)
    {
        try {
            $this->madelineProto->users->getUsers(['id' => [$telegram->getPeer()]]);
        } catch (\Exception $e) {
            //TODO Logs
            return;
        }
        usleep(1200 * 1000);
        $this->madelineProto->messages->sendMessage([
            'no_webpage' => true,
            'peer' => $telegram->getPeer(),
            'message' => $telegram->getMessage(),
            'parse_mode' => 'html',
        ]);
    }
}
