<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Payment\Spend;


use App\Entity\Community;
use App\Entity\User;
use App\Service\Payment\PaymentSpendInterface;
use Doctrine\ORM\EntityManagerInterface;

class Manual implements PaymentSpendInterface
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var int
     */
    private $value;
    /**
     * @var Community|null
     */
    private $community;

    /**
     * Manual constructor.
     * @param User $user
     * @param int $value
     * @param Community|null $community
     */
    public function __construct(User $user, int $value, ?Community $community = null)
    {
        $this->user = $user;
        $this->value = abs($value);
        $this->community = $community;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('Manual SPEND VALUE=%d, to USER ID=%d WITH COMMUNITY ID=' . ($this->community ? $this->community->getId() : 'null'),
            $this->value,
            $this->user->getId(),
            $this->getCommunity()
        );
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function transactional(EntityManagerInterface $entityManager): void
    {
        // TODO: Implement transactional() method.
    }

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community
    {
        return $this->community;
    }
}