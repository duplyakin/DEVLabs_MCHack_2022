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


use App\Entity\UserCommunityBalance;
use App\Exception\Balance\BalanceHandlerException;
use App\Service\Payment\PaymentDepositInterface;
use App\Service\Payment\PaymentInterface;
use App\Service\Payment\PaymentSpendInterface;
use App\Service\Payment\PaymentWithdrawInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class BalanceHandlerService
 * @package App\Service\Balance
 */
class BalanceHandlerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var BalanceTransactionService
     */
    private $transactionService;
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * BalanceHandlerService constructor.
     * @param EntityManagerInterface $entityManager
     * @param BalanceTransactionService $transactionService
     * @param ManagerRegistry $registry
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BalanceTransactionService $transactionService,
        ManagerRegistry $registry
    )
    {
        $this->entityManager = $entityManager;
        $this->transactionService = $transactionService;
        $this->registry = $registry;
    }

    /**
     * @param PaymentInterface $payment
     * @return bool
     * @throws BalanceHandlerException
     * @throws \Throwable
     */
    public function handle(PaymentInterface $payment): bool
    {
        if ($payment instanceof PaymentDepositInterface) {
            return $this->deposit($payment);
        }

        if ($payment instanceof PaymentWithdrawInterface) {
            return $this->withdraw($payment);
        }

        if ($payment instanceof PaymentSpendInterface) {
            return $this->withdraw($payment);
        }

        throw new BalanceHandlerException('Payment must be implement Deposit or Withdraw Interface');
    }

    /**
     * @param PaymentInterface $payment
     * @return bool
     * @throws \Throwable
     */
    protected function deposit(PaymentInterface $payment): bool
    {
        $this->entityManager->beginTransaction();
        try {
            $this->depositBalance($payment);
            $payment->transactional($this->entityManager);
            $this->transactionService->add($payment, $this->entityManager);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return true;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            if (!$this->entityManager->isOpen()) {
                $this->registry->resetManager();
            }
            throw $e;
        }
    }

    /**
     * @param PaymentInterface $payment
     * @return bool
     * @throws \Throwable
     */
    protected function withdraw(PaymentInterface $payment): bool
    {
        $this->entityManager->beginTransaction();
        try {
            $this->spendBalance($payment);
            $payment->transactional($this->entityManager);
            $this->transactionService->add($payment, $this->entityManager);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return true;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            if (!$this->entityManager->isOpen()) {
                $this->registry->resetManager();
            }
            throw $e;
        }
    }

    /**
     * @param PaymentInterface $payment
     * @throws BalanceHandlerException
     */
    protected function depositBalance(PaymentInterface $payment)
    {
        $this->setBalance($payment->getValue(), $payment);
    }

    /**
     * @param PaymentInterface $payment
     * @throws BalanceHandlerException
     */
    protected function spendBalance(PaymentInterface $payment)
    {
        $this->setBalance(-$payment->getValue(), $payment);
    }

    /**
     * @param int $value
     * @param PaymentInterface $payment
     * @return void
     * @throws BalanceHandlerException
     */
    protected function setBalance(int $value, PaymentInterface $payment)
    {
        if ($payment->getCommunity()) {
            /** @var UserCommunityBalance $communityBalance */
            $communityBalance = $payment->getUser()->getBalanceByCommunity($payment->getCommunity());
            if (!$communityBalance) {
                $communityBalance = new UserCommunityBalance();
                $communityBalance->setUser($payment->getUser());
                $communityBalance->setCommunity($payment->getCommunity());
            }
            $value = (int)$communityBalance->getValue() + $value;
            $communityBalance->setValue($value);
            if ($value < 0) {
                throw new BalanceHandlerException('No funds COMMUNITY#' . $payment->getCommunity()->getId() . ', USER#' . $payment->getUser()->getId());
            }
            $this->entityManager->persist($communityBalance);
            return;
        }

        $value = $payment->getUser()->getBalance() + $value;
        if ($value < 0) {
            throw new BalanceHandlerException('No funds USER#' . $payment->getUser()->getId());
        }
        $payment->getUser()->setBalance($value);
        $this->entityManager->persist($payment->getUser());
    }

}