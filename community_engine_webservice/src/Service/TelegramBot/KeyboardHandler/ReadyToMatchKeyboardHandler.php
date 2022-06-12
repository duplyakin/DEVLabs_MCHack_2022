<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot\KeyboardHandler;


use App\Entity\User;
use App\Message\TelegramAnswerCallbackMessage;
use App\Message\TelegramBotSendMessage;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TelegramBot\Api\Types\CallbackQuery;

class ReadyToMatchKeyboardHandler implements KeyboardHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * ReadyToMatchKeyboardHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->bus = $this->container->get('messenger.default_bus');
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return string
     */
    public static function getCallbackName(): string
    {
        return 'ready_to_match';
    }

    /**
     * @param CallbackQuery $query
     * @throws \Throwable
     */
    public function handle(CallbackQuery $query): void
    {
        $repository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $repository->findOneBy([
            'telegramId' => $query->getFrom()->getId(),
        ]);

        if (!$user) {
            return;
        }

        if ($user->getReadyToMatch()) {
            $event = new TelegramAnswerCallbackMessage(
                $query->getId(),
                'Вы уже подтвердили участие ранее!',
                true
            );
            $this->bus->dispatch($event);
            return;
        }

        $user->setReadyToMatch(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $event = new TelegramAnswerCallbackMessage(
            $query->getId(),
            'Спасибо! Вы успешно подтвердили участие!',
            true
        );

        $this->bus->dispatch($event);

        $eventSend = new TelegramBotSendMessage(
            $query->getFrom()->getId(),
            'Вы успешно подтвердили участие.'
        );
        $this->bus->dispatch($eventSend);
    }
}