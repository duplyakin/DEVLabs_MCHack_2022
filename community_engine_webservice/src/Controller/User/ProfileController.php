<?php

namespace App\Controller\User;

use App\Entity\Answer;
use App\Entity\CallUser;
use App\Entity\Question;
use App\Entity\Review;
use App\Entity\User;
use App\Event\CreateUserEvent;
use App\Form\UserType;
use App\Repository\CallRepository;
use App\Repository\CommunityRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use App\Service\CommunityService;
use App\Service\FileUploaderService;
use App\Service\ProfileService;
use App\Service\SecurityService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AboutController
 * @package App\Controller\User
 */
class ProfileController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ProfileService
     */
    protected $profileService;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FileUploaderService
     */
    protected $fileUploaderService;

    /**
     * @var CommunityService
     */
    private $communityService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SecurityService
     */
    private $securityService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AboutController constructor.
     * @param EntityManagerInterface $entityManager
     * @param ProfileService $profileService
     * @param SessionInterface $session
     * @param FileUploaderService $fileUploaderService
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityService $securityService
     * @param CommunityService $communityService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProfileService $profileService,
        SessionInterface $session,
        FileUploaderService $fileUploaderService,
        EventDispatcherInterface $eventDispatcher,
        SecurityService $securityService,
        CommunityService $communityService,
        TranslatorInterface $translator
    )
    {
        $this->entityManager = $entityManager;
        $this->profileService = $profileService;
        $this->session = $session;
        $this->fileUploaderService = $fileUploaderService;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityService = $securityService;
        $this->communityService = $communityService;
        $this->translator = $translator;
    }

    /**
     * @Route("/user/profile/photo-upload", name="user_profile_photo_upload")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function photoUpload(Request $request)
    {
        if (!$request->files->get('photo')) {
            return $this->json([
                'status' => 'error',
                'errorText' => $this->translator->trans('You must choose a photo'),
            ]);
        }
        if ($request->files->get('photo')->getSize() > (1024 * 1024)) {
            return $this->json([
                'status' => 'error',
                'errorText' => $this->translator->trans('File size should not exceed {size}Mb', [
                    '{size}' => 1,
                ]),
            ]);
        }

        if (!in_array($request->files->get('photo')->getMimeType(), [
            'image/png',
            'image/jpg',
            'image/jpeg'
        ])) {
            return $this->json([
                'status' => 'error',
                'errorText' => $this->translator->trans('Wrong image format'),
            ]);
        }

        if ($name = $this->fileUploaderService->move($request->files->get('photo'))) {
            $this->fileUploaderService->remove($this->getUser());
            $this->getUser()->setPicture($name);
            $this->entityManager->persist($this->getUser());
            $this->entityManager->flush();
            return $this->json([
                'status' => 'success',
                'url' => $this->getUser()->getPictureurl(),
            ]);
        }
        return $this->json([
            'status' => 'error',
            'errorText' => $this->translator->trans('Unknown error, please contact to administrator'),
        ]);
    }

    /**
     * @Route("/user/profile", name="user_profile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        if (!$this->getUser()->getProfileComplete()) {
            return $this->redirectToRoute('user_profile_create');
        }

        $this->profileService->setAnswers($this->getUser(), $request);
        $this->profileService->applyCustomAnswers($this->getUser(), $request);
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($this->getUser());
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('Profile updated successfully!'));
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/profile/notify", name="user_profile_notify")
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated
     */
    public function notify()
    {
        return $this->redirectToRoute('user_communities');
    }

    /**
     * @Route("/user/profile/notification", name="user_profile_notification")
     * @param CommunityRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notification(CommunityRepository $repository)
    {
        if (!$this->getUser()->getProfileComplete()) {
            return $this->redirectToRoute('user_profile_create');
        }

        /** @var User $user */
        $user = $this->getUser();
        $communities = $repository->findByUserWithCommunitySetting($user);
        return $this->render('user/profile/notification.html.twig', [
            'communities' => $communities,
        ]);
    }

    /**
     * @Route("/user/profile/delete-account", name="user_profile_delete_account")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAccount(Request $request, TokenStorageInterface $tokenStorage)
    {
        if ($request->get('delete-account-phrase') == 'delete my account') {
            $this->profileService->dropUserAccount($this->getUser(), $request);
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            return $this->render('user/profile/delete-account.html.twig');
        }
        $this->addFlash('danger', 'Verify phrase');
        return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/user/profile/check-email", name="user_profile_check_email", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('email-token', $token)) {
            $email = $request->get('email');
            /** @var UserRepository $repository */
            $repository = $this->entityManager->getRepository(User::class);
            $criteria = Criteria::create()
                ->orWhere(Criteria::expr()->eq('email', $email))
                ->orWhere(Criteria::expr()->eq('emailAlt', $email));

            if ($repository->matching($criteria)->count() > 0) {
                return new JsonResponse('Этот адрес уже используется в системе');
            }
            return new JsonResponse(true);
        }

        return new JsonResponse('Error check email. Please contact support. ');
    }

    /**
     * @Route("/user/profile/switch-ready", name="user_profile_switch_ready", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function switchReady(Request $request)
    {
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('ready-token', $token)) {
            /** @var User $user */
            $user = $this->getUser();
            $url = $request->request->get('url');
            $community = $user->getCommunities()->get($url);
            if (!$community) {
                return new JsonResponse('Error request');
            }

            $this->profileService->switchReady($user, $community);
            return new JsonResponse(true);
        }
        return new JsonResponse('Error request');
    }

    /**
     * @Route("/user/profile/switch-notify", name="user_profile_switch_notify", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function switchNotify(Request $request)
    {
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('notify-token', $token)) {
            /** @var User $user */
            $user = $this->getUser();
            $url = $request->request->get('url');
            $community = $user->getCommunities()->get($url);
            if (!$community) {
                return new JsonResponse('Error request');
            }

            $this->profileService->switchNotify($user, $community);
            return new JsonResponse(true);
        }
        return new JsonResponse('Error request');
    }

    /**
     * @Route("/user/profile/rate", name="user_profile_rate", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param CallRepository $callRepository
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function rate(
        Request $request,
        UserRepository $userRepository,
        CallRepository $callRepository,
        ValidatorInterface $validator
    )
    {
        $rate = $request->request->get('rate');
        $user = $userRepository->find($request->request->get('user'));
        $call = $callRepository->findOneBy([
            'uuid' => $request->request->get('connect'),
        ]);
        if ($rate && $user && $call && $this->isCsrfTokenValid('rate-token', $request->request->get('token'))) {
            $review = new Review();
            /** @var User $currentUser */
            $currentUser = $this->getUser();
            if ($call->getUsers()->filter(function (CallUser $callUser) use ($user, $currentUser) {
                    return in_array($callUser->getUser()->getId(), [
                        $user->getId(), $currentUser->getId()
                    ]);
                })->count() != 2) {
                return new JsonResponse('Error request');
            }
            $rate = $rate == -1 ? null : (int)$rate;
            $review->setConnect($call);
            $review->setUser($currentUser);
            $review->setRate($rate);
            $review->setIsSuccessful((bool)$rate);
            $review->setRateTo($user);

            if ($validator->validate($review)->count()) {
                return new JsonResponse('Error request');
            }
            $this->entityManager->persist($review);
            $this->entityManager->flush();
            return new JsonResponse(true);
        }
        return new JsonResponse('Error request');
    }

    /**
     * @Route("/user/profile/questions", name="user_profile_questions")
     * @param Request $request
     * @param CommunityRepository $communityRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function questions(Request $request, CommunityRepository $communityRepository)
    {
        /** @var User $user */
        $user = $this->getUser();
        $community = $communityRepository->findOneBy([
            'url' => $request->get('community'),
        ]);

        if (!$community) {
            $community = $this->communityService->getCommunity();
        }

        if (!$community) {
            throw new NotFoundHttpException();
        }

        $setting = $this->profileService->getSettingOrCreate($user, $community);
        if ($setting->getQuestionComplete() && $user->getCommunities()->contains($community)) {
            return $this->communityService->clearCurrentCommunity(
                $this->redirectToRoute('user_communities')
            );
        }

        $token = $request->get('token', false);
        $lookingFor = $request->get('looking_for', false);
        if (
            $lookingFor &&
            $request->getMethod() == 'POST' &&
            $token &&
            $this->isCsrfTokenValid('questions-token', $token)
        ) {
            $this->profileService->setAnswers($user, $request);
            $this->profileService->saveLookingFor($user, $community, $lookingFor);
            $user->addCommunity($community);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->profileService->setQuestionComplete(true, $user, $community);
            return $this->redirectToRoute('user_profile_create');
        }

        /** @var QuestionRepository $repository */
        $repository = $this->entityManager->getRepository(Question::class);
        $questions = $repository->findAllByAnswerType($community, Answer::TYPE_PUBLIC, [
            Question::TAG_PROFILE_FILL_FIRST_SCREEN,
        ]);

        return $this->render('user/profile/questions.html.twig', [
            'questions' => $questions,
            'user' => $user,
            'community' => $community,
        ]);
    }

    /**
     * @Route("/user/profile/create", name="user_profile_create")
     * @param Request $request
     * @param CommunityRepository $communityRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, CommunityRepository $communityRepository)
    {
        if ($this->getUser()->getProfileComplete()) {
            return $this->communityService->clearCurrentCommunity(
                $this->redirectToRoute('user_communities')
            );
        }

        $community = $communityRepository->findOneBy([
            'url' => $request->get('community'),
        ]);

        if (!$community) {
            $community = $this->communityService->getCommunity();
        }

        if (!$community) {
            throw new NotFoundHttpException();
        }

        /** @var QuestionRepository $repository */
        $repository = $this->entityManager->getRepository(Question::class);
        $questions = $repository->findAllByAnswerType($community, Answer::TYPE_PUBLIC, [
            Question::TAG_PROFILE_FILL_INFO_SCREEN,
        ]);

        $userForm = $this->createForm(UserType::class, $this->getUser());
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            /** @var User $user */
            $user = $userForm->getData();
            $user->addCommunity($community);
            $user->setProfileComplete(true);
            $this->profileService->applyCustomAnswers($user, $request);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $event = new CreateUserEvent($user, $community);
            $this->eventDispatcher->dispatch($event);

            $response = $this->redirectToRoute('user_communities', [
                'welcome' => 1,
            ]);

            return $this->communityService->clearCurrentCommunity($response);

        }

        return $this->render('user/profile/create.html.twig', [
            'form' => $userForm->createView(),
            'questions' => $questions,
        ]);
    }
}
