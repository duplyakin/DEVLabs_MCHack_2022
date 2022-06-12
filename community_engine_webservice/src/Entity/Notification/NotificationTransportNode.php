<?php

namespace App\Entity\Notification;

use App\Repository\Notification\NotificationTransportNodeRepository;
use App\Service\Notification\Transport\Email;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationTransportNodeRepository::class)
 */
class NotificationTransportNode
{
    const TRANSPORT_EMAIL = 'App\Service\Notification\Transport\Email';
    const TRANSPORT_TELEGRAM_NATIVE = 'App\Service\Notification\Transport\TelegramNative';
    const TRANSPORT_TELEGRAM_BOT = 'App\Service\Notification\Transport\TelegramBot';

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
