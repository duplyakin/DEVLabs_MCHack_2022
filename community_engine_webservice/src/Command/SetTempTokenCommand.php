<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetPublicidCommand
 * @package App\Command
 */
class SetTempTokenCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:set-token';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SetPublicidCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(?string $name = null, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Generate temp token for users');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        foreach ($users as $user) {
            if (!empty($user->getTempToken())) {
                continue;
            }

            $user->setTempToken(uniqid("", true));
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
