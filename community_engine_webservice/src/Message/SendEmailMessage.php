<?php

namespace App\Message;

use Symfony\Component\Mime\Address;

/**
 * Class SendEmailMessage
 * @package App\Message
 */
final class SendEmailMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    /**
     * @var string
     */
    private $subject;
    /**
     * @var
     */
    private $from;
    /**
     * @var string
     */
    private $to;
    /**
     * @var string
     */
    private $body;
    /**
     * @var string
     */
    private $replyTo;
    /**
     * @var string
     */
    private $attach;

    /**
     * SendEmailMessage constructor.
     * @param string $subject
     * @param string|null $to
     * @param string $body
     * @param null $replyTo
     * @param array|string|null $from
     * @param null $attach
     */
    public function __construct(
        string $subject,
        ?string $to,
        string $body,
        $replyTo = null,
        $from = null,
        $attach = null
    )
    {
        $this->subject = $subject;
        $this->to = $to;
        $this->body = $body;
        $this->from = $from;
        $this->replyTo = $replyTo;
        $this->attach = $attach;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getFrom(): ?Address
    {
        if ($this->from instanceof Address) {
            return $this->from;
        }
        return null;
    }

    public function getReplyTo(): ?Address
    {
        if ($this->replyTo instanceof Address) {
            return $this->replyTo;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getAttach(): ?string
    {
        return $this->attach;
    }
}
