<?php

namespace App\Command;

use App\Entity\User;
use App\Message\SendTelegramMessage;
use App\Service\Notification\TelegramNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class TelegramTestCommand
 * @package App\Command
 */
class TelegramTestCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:telegram-test';

    private $notificationService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TelegramTestCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Test telegram send message');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dd('unavailable');

        $helper = $this->getHelper('question');
        $questionPeer = new Question('Please enter userId: ');
        $userId = $helper->ask($input, $output, $questionPeer);

        $questProvider = new Question('Please enter provider: ');
        $provider = $helper->ask($input, $output, $questProvider);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)
            ->find($userId);

        return Command::SUCCESS;
    }
}
