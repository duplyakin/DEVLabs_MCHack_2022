<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Notification;


use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\Community;
use App\Entity\Notification\NotificationTransport;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Exception\Notification\NotificationDisabledException;
use App\Repository\CallUserRepository;
use App\Security\TokenAuthenticator;
use App\Service\Notification\Transport\TelegramBot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class RenderNotificationService
 * @package App\Service\Notification
 */
class RenderNotificationService
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RenderNotificationService constructor.
     * @param Environment $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager
    )
    {
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     * @param NotificationTransport $transport
     * @param Collection $users
     * @param array $meta
     * @param Community|null $community
     * @param null|string $transportClass
     * @return string
     * @throws NotificationDisabledException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderByEntity(
        User $user,
        NotificationTransport $transport,
        Collection $users,
        array $meta,
        ?Community $community,
        ?string $transportClass = null
    ): string
    {
        return $this->render(
            $transport->getBody(),
            $user,
            $users,
            $meta,
            $community,
            $this->getCommunitiesByUser($user),
            $transport->getMeta(),
            $transportClass
        );
    }

    /**
     * @param string $body
     * @param User $user
     * @param Collection $users
     * @param array $meta
     * @param Community|null $community
     * @param \Doctrine\Common\Collections\Collection|Community[] $communities
     * @param array $transportMeta
     * @param null|string $transportClass
     * @return string
     * @throws NotificationDisabledException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(
        string $body,
        User $user,
        Collection $users,
        array $meta,
        ?Community $community,
        $communities,
        array $transportMeta = [],
        ?string $transportClass = null
    ): string
    {
        if (
            is_null($community) && $communities->isEmpty() &&
            !(
                isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY], $meta[NotificationEvent::CONNECT_OBJECT_KEY]) &&
                $meta[NotificationEvent::CONNECT_OBJECT_KEY] instanceof Call &&
                $meta[NotificationEvent::IS_CONNECT_EVENT_KEY]
            )
        ) {
            throw new NotificationDisabledException();
        }

        //TODO REMOVE AFTER HUB
        $allowTelegramBotMessage = true;
        if (
            $transportClass == TelegramBot::class &&
            isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY], $meta[NotificationEvent::CONNECT_OBJECT_KEY]) &&
            $meta[NotificationEvent::CONNECT_OBJECT_KEY] instanceof Call &&
            $meta[NotificationEvent::IS_CONNECT_EVENT_KEY] &&
            $user->isUseTelegram()
        ) {
            /** @var CallUserRepository $cUserRepository */
            $cUserRepository = $this->entityManager->getRepository(CallUser::class);
            $cUsers = $cUserRepository->findUserByPartnerChatId($user->getTelegramId());
            $cUsers = new ArrayCollection($cUsers);
            $allowTelegramBotMessage = !($cUsers->count() > 1 or $cUsers->isEmpty());
        }
        //////////////

        $template = $this->twig->createTemplate($body);

        $personalAreaUrl = $this->urlGenerator->generate('user_communities', [
            TokenAuthenticator::QUERY_KEY => $user->getTempToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->twig->render($template, [
            'user' => $user,
            'users' => isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY]) && $meta[NotificationEvent::IS_CONNECT_EVENT_KEY] ? $users : [],
            'meta' => $meta,
            'transportMeta' => $transportMeta,
            'community' => $community,
            'communities' => $communities,

            'COMMUNITY_NAME' => $community ? $community->getTitle() : '',
            'USER_FULLNAME' => $user->getFullName(),
            'PA_URL' => $personalAreaUrl,
            'ALLOW_TELEGRAM_BOT_MESSENGER' => $allowTelegramBotMessage,
        ]);
    }

    /**
     * @param User $user
     * @return \Doctrine\Common\Collections\Collection|Community[]
     */
    protected function getCommunitiesByUser(User $user)
    {
        return $user->getCommunities()->filter(function (Community $community) use ($user) {
            $setting = $user->getSettingsByCommunity($community);
            return $setting && $setting->getQuestionComplete() && $setting->getSendNotifications();
        });
    }
}