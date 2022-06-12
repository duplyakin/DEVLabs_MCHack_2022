<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;


use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Answer;
use App\Entity\Community;
use App\Entity\Notification\NotificationNode;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserAction;
use App\Entity\UserCommunitySetting;
use App\Event\NotificationEvent;
use App\Repository\AnswerRepository;
use App\Repository\CommunityRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use App\Service\Notification\Transport\TelegramBot;
use App\Service\Notification\Transport\TelegramNative;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ProfileService
 * @package App\Service
 */
class ProfileService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var CommunityService
     */
    private $communityService;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ProfileService constructor.
     * @param EntityManagerInterface $entityManager
     * @param CommunityService $communityService
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CommunityService $communityService,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->communityService = $communityService;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     * @param Request $request
     * @return ProfileService
     */
    public function applyCustomAnswers(User $user, Request $request): ProfileService
    {
        $customAnswers = $request->request->get('customAnswers', []);
        foreach ($customAnswers as $questionId => $customAnswerText) {
            $title = preg_replace("/[^А-Я-а-яA-Za-z0-9\-?!\s]/u", "", $customAnswerText);
            if (empty($title)) {
                continue;
            }
            $answer = $this->findOrCreateAnswerFromText($title, (int)$questionId);
            if ($answer instanceof Answer) {
                $user->addAnswer($answer);
            }
        }
        return $this;
    }

    /**
     * @param User $user
     * @param Community|null $community
     * @return RedirectResponse
     */
    public function redirect(User $user, ?Community $community = null)
    {
        $community = $community ?? $this->communityService->getCommunity();

        if ($user->getCommunities()->isEmpty()) {
            return new RedirectResponse($this->generateUrl('user_profile_questions', [
                'community' => $community->getUrl(),
            ]), 302);
        }

        /** @var UserCommunitySetting $setting */
        $setting = $user->getSettingsByCommunity($community);

        if (
            $user->getProfileComplete() &&
            (
                ($setting && $setting->getQuestionComplete() && $user->getCommunities()->contains($community)) ||
                ($community->getIsDefault() && !$user->getCommunities()->isEmpty())
            )
        ) {
            return new RedirectResponse($this->generateUrl('user_communities'), 302);
        }

        if (($setting && !$setting->getQuestionComplete()) || !$user->getCommunities()->contains($community)) {
            return new RedirectResponse($this->generateUrl('user_profile_questions', [
                'community' => $community->getUrl(),
            ]), 302);
        }

        if (!$user->getProfileComplete()) {
            return new RedirectResponse($this->generateUrl('user_profile_create'), 302);
        }
    }

    /**
     * @param bool $value
     * @param User $user
     * @param Community $community
     */
    public function setReady(bool $value, User $user, Community $community)
    {
        if ($value) {
            $this->createDeferredReadyNotification($user, $community);
        }
        /** @var UserCommunitySetting $setting */
        $setting = $this->getSettingOrCreate($user, $community);
        $setting->setReady($value);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param Community $community
     */
    public function switchReady(User $user, Community $community)
    {
        /** @var UserCommunitySetting $setting */
        $setting = $this->getSettingOrCreate($user, $community);
        if (!$setting->getReady()) {
            $this->createDeferredReadyNotification($user, $community);
        }
        $setting->setReady(!$setting->getReady());
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param Community $community
     */
    public function switchNotify(User $user, Community $community)
    {
        /** @var UserCommunitySetting $setting */
        $setting = $this->getSettingOrCreate($user, $community);
        $setting->setSendNotifications(!$setting->getSendNotifications());
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param Community $community
     * @param string $lookingFor
     */
    public function saveLookingFor(User $user, Community $community, string $lookingFor)
    {
        /** @var UserCommunitySetting $setting */
        $setting = $this->getSettingOrCreate($user, $community);
        $setting->setLookingFor($lookingFor);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    /**
     * @param bool $value
     * @param User $user
     * @param Community $community
     */
    public function setQuestionComplete(bool $value, User $user, Community $community)
    {
        /** @var UserCommunitySetting $setting */
        $setting = $this->getSettingOrCreate($user, $community);
        $setting->setQuestionComplete($value);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param Community $community
     * @return UserCommunitySetting
     */
    public function getSettingOrCreate(User $user, Community $community)
    {
        /** @var UserCommunitySetting $setting */
        $setting = $user->getSettingsByCommunity($community);

        if (!$setting) {
            $setting = new UserCommunitySetting();
            $setting->setUser($user);
            $setting->setCommunity($community);
            $user->addUserCommunitySetting($setting);
        }

        return $setting;
    }

    /**
     * @param User $user
     * @param Community $community
     */
    protected function createDeferredReadyNotification(User $user, Community $community)
    {
        $event = (new NotificationEvent())
            ->setTransports([
                $user->isUseTelegram() ? TelegramBot::class : TelegramNative::class,
            ])
            ->setUsers(new ArrayCollection([$user]))
            ->setCommunity($community)
            ->setDeferred('DEFERRED_' . $user->getId() . '_' . $community->getId())
            ->setEventType(NotificationNode::EVENT_TYPE_DEFERRED_SET_READY);

        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     * @param string $route
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @param string $title
     * @param int $questId
     * @return Answer|null
     */
    protected function findOrCreateAnswerFromText(string $title, int $questId): ?Answer
    {
        /** @var QuestionRepository $repositoryQuestion */
        $repositoryQuestion = $this->entityManager->getRepository(Question::class);
        $question = $repositoryQuestion->find($questId);

        if (!$question || empty($title)) {
            return null;
        }

        /** @var AnswerRepository $repositoryAnswer */
        $repositoryAnswer = $this->entityManager->getRepository(Answer::class);
        $answer = $repositoryAnswer->findOneBy([
            'title' => $title,
            'question' => $question,
        ]);

        if (!$answer) {
            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setType(Answer::TYPE_PRIVATE);
            $answer->setTitle($title);
        }

        return $answer;
    }

    /**
     * @param User $user
     * @param Request $request
     * @return ProfileService
     */
    public function setAnswers(User $user, Request $request): ProfileService
    {
        $answerSingleIds = $request->request->get('answerSingle', []);
        $answerMultipleIds = $request->request->get('answerMultiple', []);
        $customAnswers = $request->request->get('customAnswers', []);
        $notEmptyAnswerIds = [];
        foreach ($customAnswers as $id => $answer) {
            if (!empty($answer)) {
                $notEmptyAnswerIds[] = $id;
            }
        }
        $single = [];
        foreach ($answerSingleIds as $questionId => $answerId) {
            if (in_array($questionId, $notEmptyAnswerIds)) {
                continue;
            }
            $single[$questionId] = $answerId;
        }
        $answerMultipleIds = call_user_func_array('array_merge', $answerMultipleIds);
        $answerIds = array_merge($single, $answerMultipleIds);

        if (empty($answerIds)) {
            return $this;
        }
        /** @var AnswerRepository $repositoryAnswer */
        $repositoryAnswer = $this->entityManager->getRepository(Answer::class);
        $answers = $repositoryAnswer->findBy([
            'id' => $answerIds,
        ]);

        if (empty($answers)) {
            return $this;
        }

        $user->setAnswers($answers);
        return $this;
    }

    /**
     * @param UserInterface $user
     * @param Request $request
     */
    public function setInvitedBy(UserInterface $user, Request $request)
    {
        $invite = $request->cookies->get(User::INVITE_COOKIE_KEY);
        if (!$invite) {
            return;
        }
        $community = $request->cookies->get(User::INVITE_COMMUNITY_COOKIE_KEY);
        $invite = preg_replace("/[^A-Za-z0-9\-\.]/u", "", $invite);
        $community = preg_replace("/[^A-Za-z0-9\-]/u", "", $community);
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $invitedBy = $repository->findOneBy([
            'publicId' => $invite,
        ]);

        if (!$invitedBy) {
            return;
        }
        /** @var User $user */
        if ($invitedBy->getId() == $user->getId()) {
            return;
        }

        /** @var CommunityRepository $communityRepository */
        $communityRepository = $this->entityManager->getRepository(Community::class);
        $community = $communityRepository->findOneBy([
            'url' => $community,
        ]);

        $user->setInvitedToCommunity($community);
        $user->setInvitedBy($invitedBy);
    }

    /**
     * @param User $user
     * @param Request $request
     */
    public function dropUserAccount(User $user, Request $request)
    {
        $user->getUserCommunitySettings()->forAll(function ($key, UserCommunitySetting $setting) {
            $this->entityManager->remove($setting);
            return true;
        });
        $user->setFirstName(null);
        $user->setLastName(null);
        $user->setEmailAlt(null);
        $user->setEmail(null);
        $user->setLookingFor(null);
        $user->setAbout(null);
        $user->setTempToken(null);
        $user->setProfileComplete(false);

        $user->setTelegramUsername(null);
        $user->setTelegramId(null);

        $user->setGoogleRefreshToken(null);
        $user->setGoogleId(null);
        $user->setGoogleAccessToken(null);

        $user->setFacebookRefreshToken(null);
        $user->setFacebookId(null);
        $user->setFacebookLink(null);
        $user->setFacebookAccessToken(null);

        $user->setLinkedinLink(null);

        $action = new UserAction();
        $action->setUser($user);
        $action->setType(UserAction::TYPE_DELETE_ACCOUNT);
        $action->setInfo([
            'IP' => $request->getClientIp(),
            'USER_AGENT' => $request->headers->get('User-Agent'),
        ]);
        $this->entityManager->persist($action);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param Community|null $community
     */
    public function unsubscribe(User $user, ?Community $community)
    {
        if ($community) {
            $settings = $this->getSettingOrCreate($user, $community);
            $settings->setSendNotifications(false);
            $this->entityManager->persist($settings);
            $this->entityManager->flush();
            return;
        }

        foreach ($user->getCommunities() as $community) {
            $settings = $this->getSettingOrCreate($user, $community);
            $settings->setSendNotifications(false);
            $this->entityManager->persist($settings);
        }

        $this->entityManager->flush();
    }
}