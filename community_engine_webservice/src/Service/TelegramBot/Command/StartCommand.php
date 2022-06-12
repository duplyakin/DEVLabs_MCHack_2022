<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot\Command;


use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Notification\NotificationNode;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Repository\UserRepository;
use App\Security\TelegramUserProvider;
use App\Service\Notification\NotificationServiceInterface;
use App\Service\Notification\TelegramNotificationService;
use App\Service\Notification\Transport\TelegramBot;
use App\Service\SecurityService;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class StartCommand extends AbstractCommand
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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * StartCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityService $securityService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        SecurityService $securityService
    )
    {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '/start';
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws \Exception
     */
    public function execute(BotApi $api, Update $update)
    {
        preg_match(self::REGEXP, $update->getMessage()->getText(), $matches);
        $token = $matches[3] ?? null;
        if ($token && $this->securityService->validateToken($token)) {
            /** @var UserRepository $repository */
            $repository = $this->entityManager->getRepository(User::class);
            $user = $repository->findOneBy([
                'publicId' => $this->securityService->getData($token),
            ]);

            if (!$user) {
                return;
            }

            if ($user->getTelegramId() == $update->getMessage()->getChat()->getId()) {
                return;
            }

            $event = (new NotificationEvent())
                ->setTransports([TelegramBot::class])
                ->setUsers(new ArrayCollection([$user]))
                ->setEventType(NotificationNode::EVENT_TYPE_ATTACH_TELEGRAM_BOT);

            try {
                if (!$user->isUseTelegram()) {
                    $user->setTelegramId($update->getMessage()->getChat()->getId());
                    $user->setTelegramUsername('@' . $update->getMessage()->getChat()->getUsername());
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $event->addMeta('already', false);
                } else {
                    $event->addMeta('already', true);
                }
                $this->eventDispatcher->dispatch($event);
            } catch (\Exception $exception) {
                //TODO logs
            }

        }
    }
}