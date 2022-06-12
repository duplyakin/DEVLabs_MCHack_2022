<?php

namespace App\Message;

/**
 * Class DeferredNotificationMessage
 * @package App\Message
 */
final class DeferredNotificationMessage
{
    /**
     * @var
     */
    private $message;

    /**
     * DeferredNotificationMessage constructor.
     * @param $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return DeferredNotificationMessage
     */
    public function setMessage($message): self
    {
        $this->message = $message;
        return $this;
    }
}
