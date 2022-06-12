<?php

namespace App\Controller\User;

use App\Entity\Answer;
use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\Community;
use App\Entity\Question;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\CommunityRepository;
use App\Repository\QuestionRepository;
use App\Service\ProfileService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CommunitiesController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SecurityService
     */
    private $securityService;

    /**
     * CommunitiesController constructor.
     * @param EntityManagerInterface $entityManager
     * @param SecurityService $securityService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SecurityService $securityService
    )
    {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
    }

    /**
     * @Route("/user/communities", name="user_communities")
     * @param CommunityRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(CommunityRepository $repository, Request $request)
    {
        if (!$this->getUser()->getProfileComplete()) {
            return $this->redirectToRoute('user_profile_create');
        }

//        if ($request->getDefaultLocale() == 'en') {
//            return $this->render('user/communities/en_complete.html.twig');
//        }

        /** @var User $user */
        $user = $this->getUser();
        $communities = $repository->findByUserWithCommunitySetting($user);
        return $this->render('user/communities/index.html.twig', [
            'communities' => $communities,
            'token' => $this->securityService->createToken($user->getPublicId()),
        ]);
    }

    /**
     * @Route("/user/communities/looking-for/{url}", name="user_communities_looking_for")
     * @param Community $community
     * @param Request $request
     * @param ProfileService $profileService
     * @return JsonResponse
     */
    public function lookingFor(
        Community $community,
        Request $request,
        ProfileService $profileService
    )
    {
        if (empty($request->get('lookingFor'))) {
            return new JsonResponse([
                'message' => 'Необходимо заполнить текст',
            ], 500);
        }

        try {
            /** @var User $user */
            $user = $this->getUser();
            $token = $request->get('token', false);
            if (
                $request->getMethod() == 'POST' &&
                $token &&
                $this->isCsrfTokenValid('looking-for-token', $token)
            ) {
                if (!$user->getCommunities()->contains($community)) {
                    throw new \Exception();
                }
                $profileService->saveLookingFor($user, $community, $request->get('lookingFor'));
            }
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => 'Произошла ошибка при сохранении',
            ], 500);
        }

        return new JsonResponse([
            'message' => 'success',
        ]);
    }

    /**
     * @Route("/user/communities/questions/{community}", name="user_communities_questions")
     * @param string $community
     * @param QuestionRepository $questionRepository
     * @param CommunityRepository $communityRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ProfileService $profileService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questions(
        string $community,
        QuestionRepository $questionRepository,
        CommunityRepository $communityRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ProfileService $profileService
    )
    {
        $community = $communityRepository->findOneBy([
            'url' => $community
        ]);

        if (!$community) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $this->getUser();
        $token = $request->get('token', false);
        if (
            $request->getMethod() == 'POST' &&
            $token &&
            $this->isCsrfTokenValid('questions-token', $token)
        ) {
            $profileService->setAnswers($user, $request);
            $entityManager->persist($user);
            $entityManager->flush();
            $profileService->saveLookingFor($user, $community, $request->get('looking_for'));
            return $this->redirectToRoute('user_communities');
        }

        $questions = $questionRepository->findAllByAnswerType($community, Answer::TYPE_PUBLIC, [
            Question::TAG_PROFILE_FILL_FIRST_SCREEN,
        ]);

        $lookingFor = $request->get('looking_for') ?? (
                $user->getSettingsByCommunity($community)->getLookingFor() ?? null
            );

        return $this->render('user/communities/questions.html.twig', [
            'user' => $user,
            'questions' => $questions,
            'community' => $community,
            'lookingFor' => $lookingFor,
        ]);
    }
}
