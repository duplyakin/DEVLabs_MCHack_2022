<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserCommunityBalance;
use App\Entity\UserCommunitySetting;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MigrateSettingsCommand
 * @package App\Command
 */
class MigrateSettingsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:migrate:settings';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * MigrateSettingsCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     * @param ManagerRegistry $registry
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager,
        ManagerRegistry $registry
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->registry = $registry;
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setDescription('Migrate user settings');
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
//        /** @var UserRepository $userRepository */
//        $userRepository = $this->entityManager->getRepository(User::class);
//
//        foreach ($userRepository->findAll() as $user) {
//            try {
//                if ($user->getCommunities()->isEmpty()) {
//                    continue;
//                }
//                $entity = $this->entityManager->getRepository(UserCommunitySetting::class)->findOneBy([
//                    'community' => $user->getCommunities()->first(),
//                    'user' => $user,
//                ]);
//                if ($entity) {
//                    continue;
//                }
//                $setting = new UserCommunitySetting();
//                $setting->setUser($user);
//                $setting->setCommunity($user->getCommunities()->first());
//                $setting->setQuestionComplete((bool)$user->getQuestionComplete());
//                $setting->setReady((bool)$user->getReadyToMatch());
//                $setting->setSendNotifications(!$user->getDoNotDisturb());
//                $setting->setLookingFor($user->getLookingFor());
//                $user->setProfileComplete((bool)$user->getQuestionComplete());
//
//                $this->entityManager->persist($user);
//                $this->entityManager->persist($setting);
//
//                $this->entityManager->flush();
//                $io->note('User ID=' . $user->getId() . ', Name=' . $user->getFullName() . ', settings success');
//            } catch (\Exception $exception) {
//                $io->error('ERROR: User ID=' . $user->getId() . ', Name=' . $user->getFullName() . ', Message: ' . $exception->getMessage());
//                $this->registry->resetManager();
//
//            }
//        }
//        $io->success('Success migration user settings!');
//
//        foreach ($userRepository->findAll() as $user) {
//            if (is_null($user->getBalance())) {
//                continue;
//            }
//            if ($user->getCommunities()->isEmpty()) {
//                continue;
//            }
//            $entity = $this->entityManager->getRepository(UserCommunityBalance::class)->findOneBy([
//                'community' => $user->getCommunities()->first(),
//                'user' => $user,
//            ]);
//            if ($entity) {
//                continue;
//            }
//            try {
//                $balance = new UserCommunityBalance();
//                $balance->setUser($user);
//                $balance->setCommunity($user->getCommunities()->first());
//                $balance->setValue($user->getBalance());
//
//                $this->entityManager->persist($balance);
//                $this->entityManager->flush();
//                $io->note('User ID=' . $user->getId() . ', Name=' . $user->getFullName() . ', balance success');
//            } catch (\Exception $exception) {
//                $io->error('ERROR: User ID=' . $user->getId() . ', Name=' . $user->getFullName() . ', Message: ' . $exception->getMessage());
//            }
//        }
//        $io->success('Success migration user balance!');

        return Command::SUCCESS;
    }
}
