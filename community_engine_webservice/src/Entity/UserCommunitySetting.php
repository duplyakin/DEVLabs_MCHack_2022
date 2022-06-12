<?php

namespace App\Entity;

use App\Repository\UserCommunitySettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserCommunitySettingRepository::class)
 * @ORM\Table(name="user_community_setting",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="user_community_unique_index",
 *            columns={"user_id", "community_id"})
 *    }
 * )
 */
class UserCommunitySetting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userCommunitySettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Community::class, inversedBy="userCommunitySettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $community;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ready = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $question_complete = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $send_notifications = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $looking_for;

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

    public function getReady(): ?bool
    {
        return $this->ready;
    }

    public function setReady(bool $ready): self
    {
        $this->ready = $ready;

        return $this;
    }

    public function getQuestionComplete(): ?bool
    {
        return $this->question_complete;
    }

    public function setQuestionComplete(bool $question_complete): self
    {
        $this->question_complete = $question_complete;

        return $this;
    }

    public function getSendNotifications(): ?bool
    {
        return $this->send_notifications;
    }

    public function setSendNotifications(bool $send_notifications): self
    {
        $this->send_notifications = $send_notifications;

        return $this;
    }

    public function getLookingFor(): ?string
    {
        return $this->looking_for;
    }

    public function setLookingFor(?string $looking_for): self
    {
        $looking_for = preg_replace("/[^А-Я-а-яA-Za-z0-9\)\(\:\.\,\-?!\s]/u", "", $looking_for);
        $this->looking_for = substr($looking_for, 0, 255);

        return $this;
    }
}
