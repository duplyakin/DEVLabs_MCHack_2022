<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Balance;


use App\Service\Payment\PaymentInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Interface BalanceTransactionInterface
 * @package App\Service\Balance
 */
interface BalanceTransactionInterface
{
    /**
     * @param PaymentInterface $payment
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function add(PaymentInterface $payment, EntityManagerInterface $entityManager): void;
}