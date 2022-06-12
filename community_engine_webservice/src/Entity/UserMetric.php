<?php

namespace App\Entity;

use App\Repository\UserMetricRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserMetricRepository::class)
 */
class UserMetric
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="metrics")
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=UserMetricField::class, inversedBy="metrics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $field;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return UserMetric
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return UserMetric
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getField(): ?UserMetricField
    {
        return $this->field;
    }

    public function setField(?UserMetricField $field): self
    {
        $this->field = $field;

        return $this;
    }
}
