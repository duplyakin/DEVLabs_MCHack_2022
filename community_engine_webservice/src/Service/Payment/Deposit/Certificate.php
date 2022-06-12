<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Payment\Deposit;


use App\Entity\Community;
use App\Entity\User;
use App\Exception\Balance\BalanceHandlerException;
use App\Service\Payment\PaymentDepositInterface;
use Doctrine\ORM\EntityManagerInterface;

class Certificate implements PaymentDepositInterface
{

    /**
     * @var \App\Entity\Certificate
     */
    private $certificate;
    /**
     * @var User
     */
    private $user;

    /**
     * Certificate constructor.
     * @param \App\Entity\Certificate $certificate
     * @param User $user
     */
    public function __construct(\App\Entity\Certificate $certificate, User $user)
    {
        $this->certificate = $certificate;
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('Apply CERTIFICATE ID=%d, CODE=%s, VALUE=%d, to USER ID=%d',
            $this->certificate->getId(),
            $this->certificate->getCode(),
            $this->certificate->getValue(),
            $this->user->getId()
        );
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->certificate->getValue();
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
        // Certificate can use only user
        return null;
    }

    /**
     * TODO move this code to CertificateService
     *
     * @param EntityManagerInterface $entityManager
     * @throws BalanceHandlerException
     */
    public function transactional(EntityManagerInterface $entityManager): void
    {
        if (!$this->certificate->getIsActive()) {
            throw new BalanceHandlerException('Certificate is not active');
        }

        if ($this->certificate->getUsedUsers()->contains($this->user)) {
            throw new BalanceHandlerException('Certificate has been used already before');
        }

        $used = (int)$this->certificate->getUsed() + 1;
        $this->certificate->setUsed($used);
        $this->certificate->addUsedUser($this->user);

        if ($this->certificate->getUsed() > $this->certificate->getNumberOfUses()) {
            throw new BalanceHandlerException('Certificate is not active');
        }

        if ($this->certificate->getUsed() == $this->certificate->getNumberOfUses()) {
            $this->certificate->setIsActive(false);
        }

        $entityManager->persist($this->certificate);
    }
}