<?php

namespace App\Entity;

use App\Repository\ConnectNoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConnectNoteRepository::class)
 */
class ConnectNote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Call::class, inversedBy="connectNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $connect;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="connectNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = strip_tags($content);

        return $this;
    }

    public function getConnect(): ?Call
    {
        return $this->connect;
    }

    public function setConnect(?Call $connect): self
    {
        $this->connect = $connect;

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

    public function __toString()
    {
        return $this->getContent();
    }
}
