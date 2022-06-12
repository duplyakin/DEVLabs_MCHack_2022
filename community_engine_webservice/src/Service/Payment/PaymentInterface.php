<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Payment;
use App\Entity\Community;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Interface PaymentInterface
 * @package App\Service\Payment
 */
interface PaymentInterface
{
    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getValue(): int;

    /**
     * @return User
     */
    public function getUser(): User;

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function transactional(EntityManagerInterface $entityManager): void ;
}