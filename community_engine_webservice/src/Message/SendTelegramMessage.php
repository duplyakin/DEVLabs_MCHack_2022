<?php

namespace App\Message;


/**
 * Class SendTelegramMessage
 * @package App\Message
 */
final class SendTelegramMessage
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $peer;

    /**
     * SendTelegramMessage constructor.
     * @param string $peer
     * @param string $message
     */
    public function __construct(string $peer, string $message)
    {
        $this->message = $message;
        $this->peer = $peer;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPeer(): string
    {
        return $this->peer;
    }
}
