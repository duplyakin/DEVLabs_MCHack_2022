<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CommunityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ApplyUsersToDefaultCommunityCommand
 * @package App\Command
 */
class ApplyUsersToDefaultCommunityCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CommunityService
     */
    private $communityService;

    /**
     * ApplyUsersToDefaultCommunityCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     * @param CommunityService $communityService
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager,
        CommunityService $communityService
    )
    {
        $this->entityManager = $entityManager;
        $this->communityService = $communityService;
        parent::__construct($name);
    }

    /**
     * @var string
     */
    protected static $defaultName = 'app:apply-users-to-default-community';

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Apply Users to Default Community');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::FAILURE;

//        $io = new SymfonyStyle($input, $output);
//
//        /** @var UserRepository $repository */
//        $repository = $this->entityManager->getRepository(User::class);
//        $users = $repository->findAll();
//
//        foreach ($users as $user) {
//            if ($user->getCommunities()->count() == 0) {
//                $this->communityService->applyUserToDefaultCommunities($user);
//            }
//        }
//
//        $io->success('Success');
//
//        return Command::SUCCESS;
    }
}
