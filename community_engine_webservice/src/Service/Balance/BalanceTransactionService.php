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


use App\Entity\BalanceTransaction;
use App\Service\Payment\PaymentInterface;
use Doctrine\ORM\EntityManagerInterface;

class BalanceTransactionService implements BalanceTransactionInterface
{
    /**
     * @param PaymentInterface $payment
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function add(PaymentInterface $payment, EntityManagerInterface $entityManager): void
    {
        $transaction = new BalanceTransaction();
        $transaction->setUser($payment->getUser());
        $transaction->setValue($payment->getValue());
        $transaction->setDescription($payment->getDescription());
        $transaction->setClassName(get_class($payment));

        $entityManager->persist($transaction);
    }
}