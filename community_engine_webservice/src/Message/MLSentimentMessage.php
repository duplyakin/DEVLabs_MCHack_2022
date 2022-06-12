<?php

namespace App\Message;

/**
 * Class MLSentimentMessage
 * @package App\Message
 */
final class MLSentimentMessage
{
    /**
     * @var
     */
    private $text;

    private $userId;

    /**
     * @param mixed $text
     * @return MLSentimentMessage
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $userId
     * @return MLSentimentMessage
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
