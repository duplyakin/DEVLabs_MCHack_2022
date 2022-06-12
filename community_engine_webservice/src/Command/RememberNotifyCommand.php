<?php

namespace App\Command;

use App\Entity\Community;
use App\Entity\Notification\NotificationNode;
use App\Entity\User;
use App\Event\NotificationEvent;
use App\Repository\CommunityRepository;
use App\Service\Notification\Transport\Email;
use App\Service\Notification\Transport\TelegramBot;
use App\Service\Notification\Transport\TelegramNative;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RememberNotifyCommand
 * @package App\Command
 */
class RememberNotifyCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:remember-notify';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RememberNotifyCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Remember notify command')
            ->addOption('apply');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var CommunityRepository $repository */
        $repository = $this->entityManager->getRepository(Community::class);
        /** @var Community[] $communities */
        $communities = $repository->findAllWithUsersByReady(false);
        foreach ($communities as $community) {

            if ($community->getId() == 12) {
                continue;
            }

            $users = $community->getUsers();
            if (!$input->getOption('apply')) {
                $users = $users->filter(function (User $user) {
                    return in_array($user->getActualEmail(), [
                        'strong.barnaul@gmail.com',
                        'belyaew.alexey@gmail.com',
                    ]);
                });
            }

            $event = (new NotificationEvent())
                ->setUsers($users)
                ->setTransports([
                    TelegramBot::class,
                    Email::class,
                    TelegramNative::class,
                ])
                ->setCommunity($community)
                ->setEventType(NotificationNode::EVENT_TYPE_UNREADY);
            $this->eventDispatcher->dispatch($event);
        }

        $this->entityManager->clear();
        $communities = $repository->findAllWithUsersByReady(true);
        foreach ($communities as $community) {

            if ($community->getId() == 12) {
                continue;
            }

            $users = $community->getUsers();
            if (!$input->getOption('apply')) {
                $users = $users->filter(function (User $user) {
                    return in_array($user->getActualEmail(), [
                        'strong.barnaul@gmail.com',
                        'belyaew.alexey@gmail.com',
                    ]);
                });
            }

            $event = (new NotificationEvent())
                ->setUsers($users)
                ->setTransports([
                    TelegramBot::class,
                    Email::class,
                    TelegramNative::class,
                ])
                ->setCommunity($community)
                ->setEventType(NotificationNode::EVENT_TYPE_READY);
            $this->eventDispatcher->dispatch($event);
        }

        return Command::SUCCESS;
    }
}
