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
use Symfony\Contracts\EventDispatcher\Event;

class CreateUserEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var null|Community
     */
    private $community;

    /**
     * CreateUserEvent constructor.
     * @param User $user
     * @param Community|null $community
     */
    public function __construct(User $user, ?Community $community = null)
    {
        $this->user = $user;
        $this->community = $community;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community
    {
        return $this->community;
    }
}