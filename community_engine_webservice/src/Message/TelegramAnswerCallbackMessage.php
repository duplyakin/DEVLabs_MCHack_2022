<?php

namespace App\Message;

/**
 * Class TelegramAnswerCallbackMessage
 * @package App\Message
 */
final class TelegramAnswerCallbackMessage
{
    /**
     * @var mixed
     */
    private $callbackId;
    /**
     * @var string
     */
    private $text;
    /**
     * @var bool
     */
    private $showAlert;

    /**
     * TelegramAnswerCallbackMessage constructor.
     * @param $callbackId
     * @param $text
     * @param $showAlert
     */
    public function __construct($callbackId, string $text, bool $showAlert)
    {
        $this->callbackId = $callbackId;
        $this->text = $text;
        $this->showAlert = $showAlert;
    }

    /**
     * @return mixed
     */
    public function getCallbackId()
    {
        return $this->callbackId;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getShowAlert(): bool
    {
        return $this->showAlert;
    }
}
