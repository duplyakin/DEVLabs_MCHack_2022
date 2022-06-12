<?php

namespace App\Controller\User;

use App\Entity\Certificate;
use App\Exception\Balance\BalanceHandlerException;
use App\Repository\CertificateRepository;
use App\Service\Balance\BalanceHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BalanceController extends AbstractController
{
    /**
     * @var BalanceHandlerService
     */
    private $balanceHandlerService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * BalanceController constructor.
     * @param BalanceHandlerService $balanceHandlerService
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        BalanceHandlerService $balanceHandlerService,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    )
    {
        $this->balanceHandlerService = $balanceHandlerService;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/user/balance/certificate", name="user_balance_certificate")
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Throwable
     */
    public function certificate(TranslatorInterface $translator)
    {
        /** @var CertificateRepository $repository */
        $repository = $this->entityManager->getRepository(Certificate::class);
        $certificate = $repository->findOneBy([
            'code' => $this->requestStack->getCurrentRequest()->get('code'),
        ]);

        if ($certificate instanceof Certificate) {
            $deposit = new \App\Service\Payment\Deposit\Certificate($certificate, $this->getUser());
            try {
                $this->balanceHandlerService->handle($deposit);
                $this->addFlash('success', 'Ваш баланс успешно пополнен!');
            } catch (BalanceHandlerException $e) {
                $this->addFlash('danger', $translator->trans($e->getMessage()));
            }
        } else {
            $this->addFlash('danger', $translator->trans('Certificate not found'));
        }

        return $this->redirectToRoute('user_profile_notify');
    }
}
