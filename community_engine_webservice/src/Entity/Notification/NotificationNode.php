<?php

namespace App\Entity\Notification;

use App\Repository\Notification\NotificationNodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationNodeRepository::class)
 */
class NotificationNode
{
    const EVENT_TYPE_CREATE_USER = 'EVENT_CREATE_USER';
    const EVENT_TYPE_UNREADY = 'EVENT_UNREADY';
    const EVENT_TYPE_READY = 'EVENT_READY';
    const EVENT_TYPE_READY_BEFORE_CONNECT = 'EVENT_READY_BEFORE_CONNECT';
    const EVENT_TYPE_UNREADY_BEFORE_CONNECT = 'EVENT_UNREADY_BEFORE_CONNECT';
    const EVENT_TYPE_CONNECT = 'EVENT_CONNECT';
    const EVENT_TYPE_ATTACH_TELEGRAM_BOT = 'EVENT_ATTACH_TELEGRAM_BOT';
    const EVENT_TYPE_DEFERRED_SET_READY = 'EVENT_DEFERRED_SET_READY';

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
