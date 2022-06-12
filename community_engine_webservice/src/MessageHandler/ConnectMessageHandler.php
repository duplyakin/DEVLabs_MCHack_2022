<?php

namespace App\MessageHandler;

use App\Message\ConnectMessage;
use App\Service\Metric\ConnectService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class ConnectMessageHandler
 * @package App\MessageHandler
 */
final class ConnectMessageHandler implements MessageHandlerInterface
{
    /**
     * @var ConnectService
     */
    private $connectService;

    /**
     * ConnectMessageHandler constructor.
     * @param ConnectService $connectService
     */
    public function __construct(ConnectService $connectService)
    {
        $this->connectService = $connectService;
    }

    /**
     * @param ConnectMessage $message
     * @throws \Exception
     */
    public function __invoke(ConnectMessage $message)
    {
        $this->connectService->run();
    }
}
