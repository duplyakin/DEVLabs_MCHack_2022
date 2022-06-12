<?php

namespace App\Entity\Notification;

use App\Repository\Notification\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=NotificationTransport::class, mappedBy="notification")
     */
    private $notificationTransports;

    /**
     * @ORM\ManyToOne(targetEntity=NotificationNode::class)
     * @ORM\JoinColumn(nullable=false, unique=true, referencedColumnName="value", name="node_value")
     */
    private $node;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nodeValue;

    public function __construct()
    {
        $this->notificationTransports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @return Collection|NotificationTransport[]
     */
    public function getNotificationTransports(): Collection
    {
        return $this->notificationTransports;
    }

    public function addNotificationTransport(NotificationTransport $notificationTransport): self
    {
        if (!$this->notificationTransports->contains($notificationTransport)) {
            $this->notificationTransports[] = $notificationTransport;
            $notificationTransport->setNotification($this);
        }

        return $this;
    }

    public function removeNotificationTransport(NotificationTransport $notificationTransport): self
    {
        if ($this->notificationTransports->contains($notificationTransport)) {
            $this->notificationTransports->removeElement($notificationTransport);
            // set the owning side to null (unless already changed)
            if ($notificationTransport->getNotification() === $this) {
                $notificationTransport->setNotification(null);
            }
        }

        return $this;
    }

    public function getNode(): ?NotificationNode
    {
        return $this->node;
    }

    public function setNode(?NotificationNode $node): self
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNodeValue(): string
    {
        return $this->nodeValue;
    }

    /**
     * @param mixed $nodeValue
     * @return Notification
     */
    public function setNodeValue($nodeValue): self
    {
        $this->nodeValue = $nodeValue;
        return $this;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
