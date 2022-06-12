<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 */
class Review
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Call::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $connect;

    /**
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }} to enter",
     * )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_successful;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="me_reviews")
     */
    private $rate_to;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = strip_tags($content);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

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

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getIsSuccessful(): ?bool
    {
        return $this->is_successful;
    }

    public function setIsSuccessful(?bool $is_successful): self
    {
        $this->is_successful = $is_successful;

        return $this;
    }

    public function getRateTo(): ?User
    {
        return $this->rate_to;
    }

    public function setRateTo(?User $rate_to): self
    {
        $this->rate_to = $rate_to;

        return $this;
    }
}
