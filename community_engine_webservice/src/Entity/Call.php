<?php

namespace App\Entity;

use App\Repository\CallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=CallRepository::class)
 * @ORM\Table(name="`call`")
 * @ORM\HasLifecycleCallbacks
 */
class Call
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $callDate;

    /**
     * @ORM\OneToMany(targetEntity=CallUser::class, mappedBy="callInstance")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="connect")
     */
    private $reviews;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=ConnectNote::class, mappedBy="connect")
     */
    private $connectNotes;

    /**
     * Use in crud admin as virtual field
     */
    public $userList;

    /**
     * @ORM\ManyToOne(targetEntity=Community::class, inversedBy="calls")
     */
    private $community;

    public function __construct()
    {
        $this->callDate = new \DateTime('next Thursday');
        $this->users = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->setCreatedAt(new \DateTime());
        $this->connectNotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallDate(): ?\DateTimeInterface
    {
        return $this->callDate;
    }

    public function setCallDate(?\DateTimeInterface $callDate): self
    {
        $this->callDate = $callDate;

        return $this;
    }

    /**
     * @return Collection|CallUser[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(CallUser $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCallInstance($this);
        }

        return $this;
    }

    public function removeUser(CallUser $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getCallInstance() === $this) {
                $user->setCallInstance(null);
            }
        }

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setConnect($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getConnect() === $this) {
                $review->setConnect(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return implode("\n", $this->getUsers()->toArray());
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

    /**
     * @param User $user
     * @return Collection|CallUser[]
     */
    public function getCallUsersNot(User $user)
    {
        return $this->getUsers()->filter(function (CallUser $callUser) use ($user) {
            return $callUser->getUser() && $callUser->getUser()->getId() != $user->getId();
        });
    }

    /**
     * @return Collection|ConnectNote[]
     */
    public function getConnectNotes(): Collection
    {
        return $this->connectNotes;
    }

    public function addConnectNote(ConnectNote $connectNote): self
    {
        if (!$this->connectNotes->contains($connectNote)) {
            $this->connectNotes[] = $connectNote;
            $connectNote->setConnect($this);
        }

        return $this;
    }

    public function removeConnectNote(ConnectNote $connectNote): self
    {
        if ($this->connectNotes->contains($connectNote)) {
            $this->connectNotes->removeElement($connectNote);
            // set the owning side to null (unless already changed)
            if ($connectNote->getConnect() === $this) {
                $connectNote->setConnect(null);
            }
        }

        return $this;
    }

    public function getUserObjects()
    {
        return $this->getUsers()->map(function (CallUser $callUser) {
            return $callUser->getUser();
        });
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
}
