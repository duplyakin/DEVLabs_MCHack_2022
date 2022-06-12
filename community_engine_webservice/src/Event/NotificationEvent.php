<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\Community;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class NotificationEvent
 * @package App\Event
 */
class NotificationEvent extends Event
{
    const IS_CONNECT_EVENT_KEY = '_IS_CONNECT_EVENT_KEY';
    const CONNECT_OBJECT_KEY = '_CONNECT_OBJECT_KEY';

    /**
     * @var string
     */
    private $eventType;
    /**
     * @var Collection|User[]
     */
    private $users;
    /**
     * @var array
     */
    private $transports = [];
    /**
     * @var null|Community
     */
    private $community;
    /**
     * @var string
     */
    private $deferred;
    /**
     * @var array
     */
    private $meta = [
        self::IS_CONNECT_EVENT_KEY => false,
    ];

    /**
     * @param Collection|User[] $users
     * @return NotificationEvent
     */
    public function setUsers(Collection $users): NotificationEvent
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
        return $this;
    }

    /**
     * @param array $transports
     * @return NotificationEvent
     */
    public function setTransports(array $transports): NotificationEvent
    {
        $this->transports = $transports;
        return $this;
    }

    /**
     * @param string $transport
     * @return $this
     */
    public function addTransport(string $transport): NotificationEvent
    {
        $this->transports[] = $transport;
        return $this;
    }

    /**
     * @param Community $community
     * @return NotificationEvent
     */
    public function setCommunity(Community $community): NotificationEvent
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return NotificationEvent
     */
    public function addMeta($key, $value): NotificationEvent
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * @param array $meta
     * @return NotificationEvent
     */
    public function setMeta(array $meta): NotificationEvent
    {
        $this->meta = $meta;
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
     * @return array
     */
    public function getTransports(): array
    {
        return $this->transports;
    }

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param string $eventType
     * @return NotificationEvent
     */
    public function setEventType(string $eventType): NotificationEvent
    {
        $this->eventType = $eventType;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param string $deferred
     * @return NotificationEvent
     */
    public function setDeferred(string $deferred): NotificationEvent
    {
        $this->deferred = $deferred;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeferred(): bool
    {
        return (bool)$this->deferred;
    }

    /**
     * @return string
     */
    public function getDeferred(): string
    {
        return $this->deferred;
    }
}