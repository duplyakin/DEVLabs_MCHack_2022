<?php

namespace App\Command;

use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\ConnectNote;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RemoveDoubleConnects202104Command
 * @package App\Command
 */
class RemoveDoubleConnects202104Command extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:remove-double-connects-2021-04';
    /**
     * @var string
     */
    protected static $defaultDescription = 'Remove after spam fuckup';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RemoveDoubleConnects202104Command constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(?string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //drop review, call_user, connect_note

        /** @var Call[] $calls */
        $calls = $this->entityManager->createQueryBuilder()
            ->select('c', 'cu', 'r', 'cn', 'u')
            ->from(Call::class, 'c')
            ->join('c.users', 'cu')
            ->join('cu.user', 'u')
            ->leftjoin('c.reviews', 'r')
            ->leftjoin('c.connectNotes', 'cn')
            ->where('DATE(c.created_at) = :date')
            ->setParameter(':date', '2021-04-12')
            ->getQuery()
            ->getResult();

        $exists = [];
        $double = [];
        $usersCount = [];
        $many = [];

        $io->note('Start...');

        foreach ($calls as $call) {

            $users = $call->getUserObjects();

            if ($users->isEmpty()) {
                $io->note('Empty users, DROP=' . $call->getId());
                $this->drop($call);
                continue;
            }

            $userIds = $users->map(function (User $user) {
                return $user->getId();
            })->toArray();

            if (count($userIds) != 2) {
                $io->note('Users count != 2, DROP=' . $call->getId());
                $this->drop($call);
                continue;
            }

            $userIds = array_values($userIds);
            sort($userIds);
            $key = md5($userIds[0] . $userIds[1]);

            if (isset($usersCount[$userIds[0]])) {
                isset($many[$userIds[0]]) ? $many[$userIds[0]]++ : $many[$userIds[0]] = 2;
            }

            if (isset($usersCount[$userIds[1]])) {
                isset($many[$userIds[1]]) ? $many[$userIds[1]]++ : $many[$userIds[1]] = 2;
            }

            $usersCount[$userIds[0]] = true;
            $usersCount[$userIds[1]] = true;

            if (isset($exists[$key])) {
                $io->note('Users exists(' . $userIds[0] . ',' . $userIds[1] . '), DROP=' . $call->getId());
                $this->drop($call);
                $double[] = true;
                continue;
            } else {
                $exists[$key] = true;
                $io->note('Users doesn\'t exists(' . $userIds[0] . ',' . $userIds[1] . '), ADD=' . $call->getId());
            }

        }

        $m = [];
        foreach ($many as $id => $num) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->find($id);
            if($user->getCommunities()->count() == $num) {
                continue;
            }
            $m[$id] = $num;
        }

        $ids = array_diff_key($usersCount, $many);

        $io->note('All connect count:' . count($calls));
        $io->note('All connect count_array:' . count($exists));
        $io->note('Double connect count_array:' . count($double));
        $io->note('Users count:' . count($usersCount));
        $io->note('Many connects users count:' . count($m));
        echo 'Many connects users IDs: ' . implode(',', array_keys($m));
        echo "\n\n\n";
        echo 'One connects users IDs: ' . implode(',', array_keys($ids));
        echo "\n\n\n";

        $this->entityManager->flush();

        $io->success('Success.');

        return Command::SUCCESS;
    }

    /**
     * @param Call $call
     */
    protected function drop(Call $call)
    {
        $call->getReviews()->map(function (Review $review) {
            $this->entityManager->remove($review);
        });

        $call->getConnectNotes()->map(function (ConnectNote $connectNote) {
            $this->entityManager->remove($connectNote);
        });

        $call->getUsers()->map(function (CallUser $callUser) {
            $this->entityManager->remove($callUser);
        });

        $this->entityManager->remove($call);
    }
}
