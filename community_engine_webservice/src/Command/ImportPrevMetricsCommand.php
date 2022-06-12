<?php

namespace App\Command;

use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\User;
use App\Repository\CallUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ImportPrevMetricsCommand
 * @package App\Command
 */
class ImportPrevMetricsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ImportPrevMetricsCommand constructor.
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
     * @var string
     */
    protected static $defaultName = 'app:import-prev-metrics';

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Import prev metric')
            ->addArgument('csv', InputArgument::REQUIRED)
            ->addOption('apply');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('csv');
        $io->note(sprintf('Parse file: %s', $filePath));

        $result = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $num = count($data);
                for ($c = 3; $c < $num; $c++) {

                    if (empty($data[$c])) {
                        continue;
                    }
                    $id = $data[0];
                    if (!$input->getOption('apply')) {
                        $id = '[' . $data[0] . ']' . $data[1];
                    }
                    $result[$c][$data[$c]][] = $id;
                }
            }
            fclose($handle);
        }

        $result[] = [
            [55, 46],
            [68, 28],
            [40, 65],
            [59, 69],
            [164, 139],
            [43, 51],
            [43, 55],
            [2, 53],
            [14, 2],
        ];

        $r = [];
        foreach ($result as $items) {
            foreach ($items as $col) {
                if (!isset($col[0], $col[1]) && !isset($col[2])) {
                    continue;
                }
                if (!$input->getOption('apply')) {
                    $r[] = $col;
                    continue;
                }
                $this->createConnectByIds($col[0], $col[1]);
            }
        }

        if (!$input->getOption('apply')) {
            dd($r);
        }

        $io->success('Success.');

        return Command::SUCCESS;
    }

    /**
     * @param $idFirst
     * @param $idSecond
     */
    protected function createConnectByIds($idFirst, $idSecond)
    {
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);

        foreach (explode(',', $idFirst) as $idf) {
            foreach (explode(',', $idSecond) as $ids) {
                $userFirst = $repository->find((int)$idf);
                $userSecond = $repository->find((int)$ids);

                if ($userFirst && $userSecond) {

                    $rsm = new ResultSetMapping();
                    $rsm->addScalarResult('cnt', 'cnt');

                    $res = (int)$this->entityManager->createNativeQuery('
                        select count(*) as cnt from call_user f
                        left join `call` c on f.call_instance_id = c.id
                        left join call_user s on c.id = s.call_instance_id
                        where f.user_id=:u1 and s.user_id=:u2;
                    ', $rsm)->setParameters([
                        'u1' => $userFirst->getId(),
                        'u2' => $userSecond->getId(),
                    ])->getSingleScalarResult();

                    if ($res > 0) {
                        continue;
                    }

                    $created = new \DateTime();
                    $created->modify('-4 month');
                    $connect = new Call();
                    $connect->setCreatedAt($created);
                    $connect->setCallDate($created);
                    $this->addUserToCall($connect, $userFirst);
                    $this->addUserToCall($connect, $userSecond);
                    $this->entityManager->persist($connect);
                    $this->entityManager->flush();
                }
            }
        }
    }

    /**
     * @param Call $call
     * @param User $user
     */
    public function addUserToCall(Call $call, User $user)
    {
        /** @var $user User */
        $callUser = new CallUser();
        $callUser->setUser($user);
        $call->addUser($callUser);
        $this->entityManager->persist($callUser);
        $this->entityManager->persist($user);
    }
}
