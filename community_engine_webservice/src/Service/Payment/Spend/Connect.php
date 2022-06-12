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


use App\Entity\Call;
use App\Entity\Community;
use App\Entity\User;
use App\Service\Payment\PaymentSpendInterface;
use Doctrine\ORM\EntityManagerInterface;

class Connect implements PaymentSpendInterface
{
    /**
     * @var Call
     */
    private $call;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Community|null
     */
    private $community;

    /**
     * Connect constructor.
     * @param Call $call
     * @param User $user
     * @param Community|null $community
     */
    public function __construct(Call $call, User $user, ?Community $community = null)
    {
        $this->call = $call;
        $this->user = $user;
        $this->community = $community;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('Spend CONNECT ID=%d, from USER ID=%d WITH COMMUNITY ID=' . ($this->community ? $this->community->getId() : 'null'),
            $this->call->getId(),
            $this->user->getId(),
            $this->getCommunity()
        );
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        // connect cost;
        return 1;
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