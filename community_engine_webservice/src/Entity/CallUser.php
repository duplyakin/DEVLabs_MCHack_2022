<?php

namespace App\Entity;

use App\Repository\CallUserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CallUserRepository::class)
 */
class CallUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Call::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $callInstance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $peerId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="callItem", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $telegramChatId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallInstance(): ?Call
    {
        return $this->callInstance;
    }

    public function setCallInstance(?Call $callInstance): self
    {
        $this->callInstance = $callInstance;

        return $this;
    }

    public function getPeerId(): ?string
    {
        return $this->peerId;
    }

    public function setPeerId(?string $peerId): self
    {
        $this->peerId = $peerId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isReady(): bool
    {
        return (bool) $this->getPeerId();
    }

    public function __toString()
    {
        return $this->getUser()->getFirstName() . '(' . $this->getUser()->getActualEmail() . ')';
    }

    public function getTelegramChatId(): ?int
    {
        return $this->telegramChatId;
    }

    public function setTelegramChatId(?int $telegramChatId): self
    {
        $this->telegramChatId = $telegramChatId;

        return $this;
    }
}
