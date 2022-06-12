<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot;


use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\CallUser;
use App\Entity\User;
use App\Message\TelegramBotSendMessage;
use App\Repository\CallUserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TelegramBot\Api\Types\Update;

class TelegramMessageService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var LoggerInterface
     */
    private $tgbotLogger;

    /**
     * TelegramMessageService constructor.
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param LoggerInterface $tgbotLogger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        LoggerInterface $tgbotLogger
    )
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->tgbotLogger = $tgbotLogger;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $message = $update->getMessage() ? $update->getMessage() : $update->getEditedMessage();
        /** @var CallUserRepository $cUserRepository */
        $cUserRepository = $this->entityManager->getRepository(CallUser::class);
        if (!$message->getFrom()) {
            $this->tgbotLogger->info('MESSAGE IS NULL=' . print_r($message, true));
            return;
        }
        /** @var Collection $cUsers */
        $cUsers = $cUserRepository->findUserByPartnerChatId($message->getFrom()->getId());
        $cUsers = new ArrayCollection($cUsers);
        if ($cUsers->isEmpty()) {
//            $messageText = "К сожалению, у вашего собеседника отключена возможность общения через чат-бот. Пожалуйста, свяжитесь с партнером самостоятельно.";
//            $event = new TelegramBotSendMessage($message->getFrom()->getId(), $messageText);
//            $this->bus->dispatch($event);
            return;
        }
        if ($cUsers->count() > 1) {
            $messageText = "Вам назначено более одной встречи на этой неделе. Общение через бот отключено. Пожалуйста, свяжитесь с партнером самостоятельно.";
            $event = new TelegramBotSendMessage($message->getFrom()->getId(), $messageText);
            $this->bus->dispatch($event);
            return;
        }
        $userFrom = $cUsers->first()->getUser();
        /** @var Collection $users */
        $users = $cUsers->first()->getCallInstance()->getCallUsersNot($userFrom);
        $partnerCUsers = new ArrayCollection();
        if ($users->first()) {
            $partnerCUsers = $cUserRepository->findUserByPartnerChatId($cUsers->first()->getTelegramChatId());
            $partnerCUsers = new ArrayCollection($partnerCUsers);
        }
        if ($partnerCUsers->count() > 1 or $partnerCUsers->isEmpty() or $users->count() > 1 or $users->isEmpty()) {
            $messageText = "К сожалению, у вашего собеседника отключена возможность общения через чат-бот. Пожалуйста, свяжитесь с партнером самостоятельно.";
            $event = new TelegramBotSendMessage($message->getFrom()->getId(), $messageText);
            $this->bus->dispatch($event);
            return;
        }
        if ($users->count() == 1) {
            $replyText = $message->getReplyToMessage() ? $message->getReplyToMessage()->getText() : null;
            $replyText = !empty($replyText) ? "<code>" . $replyText . "</code>\n" : '';
            $messageText = "💬 <b>" . $users->first()->getUser()->getFullName() . ":</b>\n" . $replyText . $message->getText();
            $event = new TelegramBotSendMessage($cUsers->first()->getTelegramChatId(), $messageText);
            $this->bus->dispatch($event);
        }
    }
}