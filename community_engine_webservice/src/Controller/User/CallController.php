<?php

namespace App\Controller\User;

use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\ConnectNote;
use App\Repository\CallRepository;
use App\Repository\CallUserRepository;
use App\Repository\ConnectNoteRepository;
use App\Service\Calendar\ICalService;
use App\Service\Call\ConnectCallService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CallController
 * @package App\Controller
 */
class CallController extends AbstractController
{
    /**
     * @var ConnectCallService
     */
    protected $connectCallService;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var Request
     */
    private $request;

    /**
     * CallController constructor.
     * @param ConnectCallService $connectCallService
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $request
     */
    public function __construct(
        ConnectCallService $connectCallService,
        EntityManagerInterface $entityManager,
        RequestStack $request
    )
    {
        $this->connectCallService = $connectCallService;
        $this->entityManager = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/user/network", name="call_list")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(PaginatorInterface $paginator, Request $request)
    {
        if (!$this->getUser()->getProfileComplete()) {
            return $this->redirectToRoute('user_profile_create');
        }

        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $callsQuery = $repository->findByUserQuery($this->getUser());

        $pagination = $paginator->paginate(
            $callsQuery,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/call/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/user/network/{callUuid}", name="call_detail")
     * @param string $callUuid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detail(string $callUuid, ICalService $calService)
    {
//        $calService->createICalFile();
//        dd();
//        /** @var CallRepository $repository */
//        $repository = $this->entityManager->getRepository(Call::class);
//        $call = $repository->findOneBy([
//            'uuid' => $callUuid,
//        ]);
//        if ($call instanceof Call) {
//            return $this->render('user/call/detail2.html.twig', [
////                'session_id' => $this->connectCallService->getSessionId($this->getUser(), $call),
//                'call_uuid' => $call->getUuid(),
//            ]);
//        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/user/network/note/{callUuid}/{noteId?}", name="call_save_note", methods={"POST"})
     * @param string $callUuid
     * @param int|null $noteId
     * @return JsonResponse
     */
    public function saveNote(string $callUuid, ?int $noteId)
    {
        if (!$this->request->get('content')) {
            return new JsonResponse([
                'status' => 'success',
            ]);
        }

        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $call = $repository->findOneBy([
            'uuid' => $callUuid,
        ]);

        if (!($call instanceof Call)) {
            return new JsonResponse([
                'status' => 'success',
            ]);
        }

        if ($noteId) {
            /** @var ConnectNoteRepository $repository */
            $repository = $this->entityManager->getRepository(ConnectNote::class);
            $note = $repository->findOneBy([
                'id' => $noteId,
                'user' => $this->getUser(),
            ]);
            if (!$note) {
                return new JsonResponse([
                    'status' => 'success',
                ]);
            }
        } else {
            $note = new ConnectNote();
            $note->setUser($this->getUser());
            $note->setConnect($call);
        }

        $note->setContent($this->request->get('content'));
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'noteId' => $note->getId(),
        ]);
    }

//    /**
//     * @Route("/call/peer-link/{callUuid}/{peerId}", name="call.peer-link")
//     * @param string $callUuid
//     * @param string $peerId
//     * @return JsonResponse
//     */
//    public function setPeerId(string $callUuid, string $peerId)
//    {
//        dd($peerId);
//        /** @var CallRepository $repository */
//        $repository = $this->entityManager->getRepository(Call::class);
//        $call = $repository->findOneBy([
//            'uuid' => $callUuid,
//        ]);
//        if ($call instanceof Call && $this->connectCallService->savePeerId($this->getUser(), $call, $peerId)) {
//            return new JsonResponse([
//                'state' => 'success',
//            ]);
//        }
//
//        return new JsonResponse([
//            'state' => 'error',
//        ]);
//    }

    /**
     * @Route("/user/call/peer-id/{callUuid}", name="call.peer-id")
     * @param string $callUuid
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRemotePeerId(string $callUuid)
    {
        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $call = $repository->findOneBy([
            'uuid' => $callUuid,
        ]);

        if ($call instanceof Call) {
            /** @var CallUserRepository $repositoryCallUser */
            $repositoryCallUser = $this->entityManager->getRepository(CallUser::class);
            $userCall = $repositoryCallUser->findRemoteUserCall($this->getUser(), $call);
            if (!$userCall || !$userCall->getUser()) {
                return new JsonResponse([]);
            }
            return new JsonResponse([
                'id' => $this->connectCallService->getSessionId($userCall->getUser(), $call),
            ]);
        }

        return new JsonResponse([]);
    }
}
