<?php

namespace App\Entity;

use App\Repository\CommunityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Notification\NotificationTransport;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=CommunityRepository::class)
 * @UniqueEntity("url")
 * @AppAssert\DefaultCommunity
 */
class Community
{
    /**
     *
     */
    const COOKIE_KEY = '_Community_Name';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $url;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrivate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="communities", cascade={"persist", "refresh"})
     */
    private $users;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_default;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_paid = false;

    /**
     * @ORM\OneToMany(targetEntity=NotificationTransport::class, mappedBy="community")
     */
    private $notificationTransports;

    /**
     * @ORM\ManyToMany(targetEntity=Question::class, mappedBy="communities")
     */
    private $questions;

    /**
     * @ORM\Column(type="text")
     */
    private $short_description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity=UserCommunitySetting::class, mappedBy="community")
     */
    private $userCommunitySettings;

    /**
     * @ORM\OneToMany(targetEntity=UserCommunityBalance::class, mappedBy="community")
     */
    private $userCommunityBalances;

    /**
     * @ORM\OneToMany(targetEntity=Call::class, mappedBy="community")
     */
    private $calls;

    /**
     * @ORM\OneToMany(targetEntity=MetricOrder::class, mappedBy="community")
     */
    private $metricOrders;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="invitedToCommunity")
     */
    private $invitedUsers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="manageCommunities")
     * @JoinTable(name="manager_community",
     *      joinColumns={@JoinColumn(name="community_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $managers;

    /**
     * Community constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->notificationTransports = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->userCommunitySettings = new ArrayCollection();
        $this->userCommunityBalances = new ArrayCollection();
        $this->calls = new ArrayCollection();
        $this->metricOrders = new ArrayCollection();
        $this->invitedUsers = new ArrayCollection();
        $this->managers = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Community
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     * @return Community
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    /**
     * @param bool $isPrivate
     * @return Community
     */
    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     * @return Community
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return Community
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Community
     */
    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->is_default;
    }

    /**
     * @param bool|null $is_default
     * @return Community
     */
    public function setIsDefault(?bool $is_default): self
    {
        $this->is_default = $is_default;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsPaid(): ?bool
    {
        return $this->is_paid;
    }

    /**
     * @param bool $is_paid
     * @return Community
     */
    public function setIsPaid(bool $is_paid): self
    {
        $this->is_paid = $is_paid;

        return $this;
    }

    /**
     * @return Collection|NotificationTransport[]
     */
    public function getNotificationTransports(): Collection
    {
        return $this->notificationTransports;
    }

    /**
     * @param NotificationTransport $notificationTransport
     * @return Community
     */
    public function addNotificationTransport(NotificationTransport $notificationTransport): self
    {
        if (!$this->notificationTransports->contains($notificationTransport)) {
            $this->notificationTransports[] = $notificationTransport;
            $notificationTransport->setCommunity($this);
        }

        return $this;
    }

    /**
     * @param NotificationTransport $notificationTransport
     * @return Community
     */
    public function removeNotificationTransport(NotificationTransport $notificationTransport): self
    {
        if ($this->notificationTransports->contains($notificationTransport)) {
            $this->notificationTransports->removeElement($notificationTransport);
            // set the owning side to null (unless already changed)
            if ($notificationTransport->getCommunity() === $this) {
                $notificationTransport->setCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Question $question
     * @return Community
     */
    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->addCommunity($this);
        }

        return $this;
    }

    /**
     * @param Question $question
     * @return Community
     */
    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            $question->removeCommunity($this);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    /**
     * @param string $short_description
     * @return Community
     */
    public function setShortDescription(string $short_description): self
    {
        $this->short_description = $short_description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     * @return Community
     */
    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection|UserCommunitySetting[]
     */
    public function getUserCommunitySettings(): Collection
    {
        return $this->userCommunitySettings;
    }

    /**
     * @param UserCommunitySetting $userCommunitySetting
     * @return Community
     */
    public function addUserCommunitySetting(UserCommunitySetting $userCommunitySetting): self
    {
        if (!$this->userCommunitySettings->contains($userCommunitySetting)) {
            $this->userCommunitySettings[] = $userCommunitySetting;
            $userCommunitySetting->setCommunity($this);
        }

        return $this;
    }

    /**
     * @param UserCommunitySetting $userCommunitySetting
     * @return Community
     */
    public function removeUserCommunitySetting(UserCommunitySetting $userCommunitySetting): self
    {
        if ($this->userCommunitySettings->contains($userCommunitySetting)) {
            $this->userCommunitySettings->removeElement($userCommunitySetting);
            // set the owning side to null (unless already changed)
            if ($userCommunitySetting->getCommunity() === $this) {
                $userCommunitySetting->setCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserCommunityBalance[]
     */
    public function getUserCommunityBalances(): Collection
    {
        return $this->userCommunityBalances;
    }

    /**
     * @param UserCommunityBalance $userCommunityBalance
     * @return Community
     */
    public function addUserCommunityBalance(UserCommunityBalance $userCommunityBalance): self
    {
        if (!$this->userCommunityBalances->contains($userCommunityBalance)) {
            $this->userCommunityBalances[] = $userCommunityBalance;
            $userCommunityBalance->setCommunity($this);
        }

        return $this;
    }

    /**
     * @param UserCommunityBalance $userCommunityBalance
     * @return Community
     */
    public function removeUserCommunityBalance(UserCommunityBalance $userCommunityBalance): self
    {
        if ($this->userCommunityBalances->contains($userCommunityBalance)) {
            $this->userCommunityBalances->removeElement($userCommunityBalance);
            // set the owning side to null (unless already changed)
            if ($userCommunityBalance->getCommunity() === $this) {
                $userCommunityBalance->setCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return '/upload/communities/' . $this->getLogo();
    }

    /**
     * @return Collection|Call[]
     */
    public function getCalls(): Collection
    {
        return $this->calls;
    }

    public function addCall(Call $call): self
    {
        if (!$this->calls->contains($call)) {
            $this->calls[] = $call;
            $call->setCommunity($this);
        }

        return $this;
    }

    public function removeCall(Call $call): self
    {
        if ($this->calls->removeElement($call)) {
            // set the owning side to null (unless already changed)
            if ($call->getCommunity() === $this) {
                $call->setCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MetricOrder[]
     */
    public function getMetricOrders(): Collection
    {
        return $this->metricOrders;
    }

    public function addMetricOrder(MetricOrder $metricOrder): self
    {
        if (!$this->metricOrders->contains($metricOrder)) {
            $this->metricOrders[] = $metricOrder;
            $metricOrder->setCommunity($this);
        }

        return $this;
    }

    public function removeMetricOrder(MetricOrder $metricOrder): self
    {
        if ($this->metricOrders->removeElement($metricOrder)) {
            // set the owning side to null (unless already changed)
            if ($metricOrder->getCommunity() === $this) {
                $metricOrder->setCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getInvitedUsers(): Collection
    {
        return $this->invitedUsers;
    }

    public function addInvitedUser(User $invitedUser): self
    {
        if (!$this->invitedUsers->contains($invitedUser)) {
            $this->invitedUsers[] = $invitedUser;
            $invitedUser->setInvitedToCommunity($this);
        }

        return $this;
    }

    public function removeInvitedUser(User $invitedUser): self
    {
        if ($this->invitedUsers->removeElement($invitedUser)) {
            // set the owning side to null (unless already changed)
            if ($invitedUser->getInvitedToCommunity() === $this) {
                $invitedUser->setInvitedToCommunity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(User $manager): self
    {
        if (!$this->managers->contains($manager)) {
            $this->managers[] = $manager;
            $manager->addManageCommunity($this);
        }

        return $this;
    }

    public function removeManager(User $manager): self
    {
        if ($this->managers->removeElement($manager)) {
            $manager->removeManageCommunity($this);
        }

        return $this;
    }
}
