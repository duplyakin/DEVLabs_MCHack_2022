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
use App\Message\CreateTelegramGroupMessage;
use App\Message\SendTelegramMessage;
use App\Message\DeferredNotificationMessage;
use App\Service\Notification\RenderNotificationService;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TelegramNative implements NotificationTransportInterface
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
     * Email constructor.
     * @param RenderNotificationService $renderNotification
     * @param MessageBusInterface $bus
     */
    public function __construct(
        RenderNotificationService $renderNotification,
        MessageBusInterface $bus
    )
    {
        $this->renderNotification = $renderNotification;
        $this->bus = $bus;
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
        return;

        if (
        !(
            isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY], $meta[NotificationEvent::CONNECT_OBJECT_KEY]) &&
            $meta[NotificationEvent::CONNECT_OBJECT_KEY] instanceof Call &&
            $meta[NotificationEvent::IS_CONNECT_EVENT_KEY]
        )
        ) {
            /** @var User[]|Collection $users */
            $users = $users->filter(function (User $user) {
                return ((bool)$user->getTelegramUsername() && !$user->isUseTelegram());
            });
        }

        foreach ($users as $user) {
            if (!((bool)$user->getTelegramUsername() && !$user->isUseTelegram())) {
                continue;
            }
            try {
                $messageBody = $this->renderNotification->renderByEntity(
                    $user,
                    $transport,
                    $users,
                    $meta,
                    $community,
                    TelegramNative::class
                );
                $event = new SendTelegramMessage(
                    $user->getTelegramUsername(),
                    $messageBody
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
}