<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Notification\Transport;


use App\Entity\Call;
use App\Entity\Community;
use App\Entity\Notification\NotificationTransport;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Exception\Notification\NotificationDisabledException;
use App\Message\DeferredNotificationMessage;
use App\Message\TelegramBotSendMessage;
use App\Security\TokenAuthenticator;
use App\Service\Notification\RenderNotificationService;
use App\Service\TelegramBot\Keyboard\KeyboardInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TelegramBot implements NotificationTransportInterface
{
    /**
     * @var RenderNotificationService
     */
    private $renderNotification;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Email constructor.
     * @param RenderNotificationService $renderNotification
     * @param MessageBusInterface $bus
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        RenderNotificationService $renderNotification,
        MessageBusInterface $bus,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->renderNotification = $renderNotification;
        $this->bus = $bus;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param NotificationTransport $transport
     * @param Community|null $community
     * @param Collection|User[] $users
     * @param array $meta
     * @param bool $deferred
     * @return void
     */
    public function send(
        NotificationTransport $transport,
        ?Community $community,
        Collection $users,
        array $meta,
        bool $deferred
    ): void
    {
        if (
        !(
            isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY], $meta[NotificationEvent::CONNECT_OBJECT_KEY]) &&
            $meta[NotificationEvent::CONNECT_OBJECT_KEY] instanceof Call &&
            $meta[NotificationEvent::IS_CONNECT_EVENT_KEY]
        )
        ) {
            $users = $users->filter(function (User $user) {
                return (bool)$user->isUseTelegram();
            });
        }

        foreach ($users as $user) {
            if (!$user->isUseTelegram()) {
                continue;
            }
            try {
                $messageBody = $this->renderNotification->renderByEntity(
                    $user,
                    $transport,
                    $users,
                    $meta,
                    $community,
                    TelegramBot::class
                );
                $event = new TelegramBotSendMessage(
                    $user->getTelegramId(),
                    $messageBody,
                    $this->getKeyboard($transport, $user)

                );
                if ($deferred) {
                    $event = new DeferredNotificationMessage($event);
                }
                $this->bus->dispatch($event);
            } catch (LoaderError $e) {
                //TODO logs
            } catch (RuntimeError $e) {
                //TODO logs
            } catch (SyntaxError $e) {
                //TODO logs
            } catch (NotificationDisabledException $e) {
                //TODO logs
            }
        }
    }

    protected function getKeyboard(NotificationTransport $transport, User $user): ?InlineKeyboardMarkup
    {
        $personalAreaUrl = $this->urlGenerator->generate('user_communities', [
            TokenAuthenticator::QUERY_KEY => $user->getTempToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        /** @var KeyboardInterface $keyboard */
        $keyboard = $transport->getMetaByKey('keyboard');
        if (class_exists($keyboard, true)) {
            return new InlineKeyboardMarkup($keyboard::getButtons([
                'pa_url' => $personalAreaUrl,
            ]));
        }
        return null;
    }
}