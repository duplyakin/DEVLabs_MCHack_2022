<?php

namespace App\Entity;

use App\Repository\MetricOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=MetricOrderRepository::class)
 * * @UniqueEntity(
 *     fields={"user", "withUser", "community"},
 *     errorPath="emailAlt",
 *     repositoryMethod="checkEmail",
 *     message="This email is already in use"
 * )
 */
class MetricOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="metricOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Community::class, inversedBy="metricOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $community;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $withUser;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->id;
    }

    public function getWithUser(): ?User
    {
        return $this->withUser;
    }

    public function setWithUser(?User $withUser): self
    {
        $this->withUser = $withUser;

        return $this;
    }
}
