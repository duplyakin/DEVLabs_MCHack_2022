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
use App\Entity\CallUser;
use App\Entity\Community;
use App\Entity\Notification\NotificationTransport;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Exception\Notification\NotificationDisabledException;
use App\Message\SendEmailMessage;
use App\Message\DeferredNotificationMessage;
use App\Service\Calendar\ICalService;
use App\Service\Notification\RenderNotificationService;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Email implements NotificationTransportInterface
{
    const DEFAULT_SUBJECT = 'Meetsup Notify Message';

    /**
     * @var RenderNotificationService
     */
    private $renderNotification;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var ICalService
     */
    private $calService;

    /**
     * Email constructor.
     * @param RenderNotificationService $renderNotification
     * @param MessageBusInterface $bus
     * @param Environment $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param ICalService $calService
     */
    public function __construct(
        RenderNotificationService $renderNotification,
        MessageBusInterface $bus,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        ICalService $calService
    )
    {
        $this->renderNotification = $renderNotification;
        $this->bus = $bus;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->calService = $calService;
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
        $users = $users->filter(function (User $user) {
            return (bool)$user->getActualEmail();
        });

        /** @var User $user */
        foreach ($users as $user) {
            try {
                $messageBody = $this->renderNotification->renderByEntity(
                    $user,
                    $transport,
                    $users,
                    $meta,
                    $community,
                    Email::class
                );
                $subject = $transport->getMetaByKey('subject') ?? self::DEFAULT_SUBJECT;
                $replyTo = null;
                $attach = null;
                if (
                    isset($meta[NotificationEvent::IS_CONNECT_EVENT_KEY], $meta[NotificationEvent::CONNECT_OBJECT_KEY]) &&
                    $meta[NotificationEvent::CONNECT_OBJECT_KEY] instanceof Call &&
                    $meta[NotificationEvent::IS_CONNECT_EVENT_KEY]
                ) {
                    /** @var Call $connect */
                    $connect = $meta[NotificationEvent::CONNECT_OBJECT_KEY];
                    $partners = $connect->getCallUsersNot($user);
                    /** @var CallUser $partner */
                    if (!$partners->isEmpty() && ($partner = $partners->first()) && ($replyUser = $partner->getUser())) {
                        $replyTo = $replyUser->getActualEmail() ?? null;
                    }
                    // Disable
                    // $attach = $this->calService->createICalInstance($connect, $messageBody);
                }

                $communityUrl = $community ? $community->getUrl() : null;

                $unsubscribeUrl = $this->urlGenerator->generate('app_unsubscribe', [
                    'public_id' => $user->getPublicId(),
                    'community_url' => $communityUrl,
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $messageBody = $this->twig->render('emails/base.html.twig', [
                    'body' => $messageBody,
                    'unsubscribe_link' => $unsubscribeUrl,
                ]);

                $event = new SendEmailMessage(
                    $subject,
                    $user->getActualEmail(),
                    $messageBody,
                    $replyTo ? new Address($replyTo) : null,
                    null,
                    $attach
                );
                if ($deferred) {
                    $event = new DeferredNotificationMessage($event);
                }

                $delay = array_rand([
                    1000 => null,
                    2000 => null,
                    3000 => null,
                    4000 => null,
                    5000 => null,
                    6000 => null,
                    7000 => null,
                    8000 => null,
                    9000 => null,
                    10000 => null,
                ]);

                $this->bus->dispatch($event, [
                    new DelayStamp($delay),
                ]);
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