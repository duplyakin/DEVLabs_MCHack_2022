<?php

namespace App\Command;

use App\Entity\User;
use App\Message\SendEmailMessage;
use App\Message\TelegramBotSendMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class CustomMailingCommand
 * @package App\Command
 */
class CustomMailingCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:custom-mailing';
    /**
     * @var string
     */
    protected static $defaultDescription = 'Custom mailing';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * CustomMailingCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    )
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('apply');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        /** @var User[] $communities */
        $users = $repository->findBy([
            'id' => [
                1,
                2,
                384,
                1613,
                1713,
                1521,
                1091,
                1855,
                80,
                1395,
                1861,
                826,
                431,
                406,
                900,
                762,
                1123,
                309,
                1523,
                1063,
                69,
                1815,
                660,
                108,
                1589,
                583,
                641,
                1167,
                711,
                1193,
                1204,
                1079,
                674,
                1330,
                1252,
                715,
                1872,
                964,
                125,
                1426,
                1031,
                664,
            ],
        ]);

        $tgMessage = <<<TG
🔥 Новость для сообщества Meetsup:

Дмитрий Волошин (Venture Partner в Fort Ross Ventures; Co-founder Otus) присоединился к команде Meetsup в качестве партнера по организации менторских программ. Если у вас есть запрос на поиск наставника для получения экспертизы и расширения профессиональных связей, то напишите Дмитрию в телеграм: @DVoloshin

У Дмитрия собрана сильная команда менторов. Возможно вы найдете среди них именно того, кого искали.
TG;

        $mailMessage = <<<MAIL
<h3>Новость для сообщества Meetsup:</h3>

Дмитрий Волошин (Venture Partner в Fort Ross Ventures; Co-founder Otus) присоединился к команде Meetsup в качестве партнера по организации менторских программ. Если у вас есть запрос на поиск наставника для получения экспертизы и расширения профессиональных связей, то напишите Дмитрию в телеграм: <a href="https://t.me/DVoloshin">@DVoloshin</a><br/><br/>

У Дмитрия собрана сильная команда менторов. Возможно вы найдете среди них именно того, кого искали.
MAIL;


        foreach ($users as $user) {
            if (!$input->getOption('apply') && !in_array($user->getId(), [1, 2])) {
                continue;
            }

            $eventMail = new SendEmailMessage(
                '🔥 Meetsup совместно с Fort Ross Ventures',
                $user->getActualEmail(),
                $mailMessage
            );
            $this->bus->dispatch($eventMail);

            $event = new TelegramBotSendMessage(
                $user->getTelegramId(),
                $tgMessage

            );
            $this->bus->dispatch($event);
        }

        return Command::SUCCESS;
    }
}
