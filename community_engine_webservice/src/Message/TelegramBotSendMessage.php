<?php

namespace App\Message;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

final class TelegramBotSendMessage
{
    /**
     * @var mixed
     */
    private $chatId;
    /**
     * @var InlineKeyboardMarkup
     */
    private $keyboardMarkup;
    /**
     * @var string
     */
    private $messageText;

    /**
     * TelegramBotSendMessage constructor.
     * @param $chatId
     * @param string $messageText
     * @param null|InlineKeyboardMarkup $keyboardMarkup
     */
    public function __construct($chatId, string $messageText, ?InlineKeyboardMarkup $keyboardMarkup = null)
    {
        $this->chatId = $chatId;
        $this->keyboardMarkup = $keyboardMarkup;
        $this->messageText = $messageText;
    }

    /**
     * @return mixed
     */
    public function getChatId()
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function getMessageText(): string
    {
        return $this->messageText;
    }

    /**
     * @return null|InlineKeyboardMarkup
     */
    public function getKeyboardMarkup(): ?InlineKeyboardMarkup
    {
        return $this->keyboardMarkup;
    }
}
