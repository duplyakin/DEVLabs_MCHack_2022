<?php

namespace App\Controller\User;

use App\Entity\Call;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\CallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ConnectController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * CallController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/user/connect/{callUuid}", name="user_connect")
     * @param string $callUuid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(string $callUuid)
    {
        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $call = $repository->findOneBy([
            'uuid' => $callUuid,
        ]);
        if ($call instanceof Call) {
            return $this->render('user/connect/index.html.twig', [
                'call_uuid' => $call->getUuid(),
            ]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/user/review/{callUuid}", name="user_review")
     * @param string $callUuid
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function review(string $callUuid, Request $request)
    {
        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $call = $repository->findOneBy([
            'uuid' => $callUuid,
        ]);
        if (!($call instanceof Call)) {
            return $this->redirectToRoute('user_profile');
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setConnect($call);
            $review->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Спасибо за ваш отзыв!');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/connect/review.html.twig', [
            'form' => $form->createView(),
            'call_uuid' => $call->getUuid(),
        ]);
    }
}
