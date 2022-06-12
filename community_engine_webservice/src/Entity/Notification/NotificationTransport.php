<?php

namespace App\Entity\Notification;

use App\Repository\Notification\NotificationTransportRepository;
use App\Entity\Community;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"community", "notification", "node"},
 *     ignoreNull=false,
 *     message="This transport is already exist."
 * )
 *
 * @ORM\Entity(repositoryClass=NotificationTransportRepository::class)
 * @ORM\Table(name="notification_transport",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="notification_transport_unique_index",
 *            columns={"community_id", "notification_id", "node_value"})
 *    }
 * )
 */
class NotificationTransport
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
     * @ORM\Column(type="array", nullable=true)
     */
    private $meta = [];

    /**
     * @ORM\ManyToOne(targetEntity=Community::class, inversedBy="notificationTransports")
     */
    private $community;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity=Notification::class, inversedBy="notificationTransports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $notification;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity=NotificationTransportNode::class)
     * @ORM\JoinColumn(nullable=false, referencedColumnName="value", name="node_value")
     */
    private $node;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nodeValue;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

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

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function getMetaByKey($key): ?string
    {
        foreach ((array)$this->getMeta() as $metaItem) {
            $metaItem = explode(':', $metaItem);
            if (isset($metaItem[1]) && $metaItem[0] == $key) {
                return $metaItem[1];
            }
        }

        return null;
    }

    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;

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

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getNode(): ?NotificationTransportNode
    {
        return $this->node;
    }

    public function setNode(?NotificationTransportNode $node): self
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
     * @return NotificationTransport
     */
    public function setNodeValue($nodeValue): self
    {
        $this->nodeValue = $nodeValue;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }
}
