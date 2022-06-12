<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ParseTgLogCommand extends Command
{
    protected static $defaultName = 'app:parse-tg-log';
    protected static $defaultDescription = 'Add a short description for your command';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var array
     */
    private $log = [];
    /**
     * @var array
     */
    private $summary = [];
    /**
     * @var array
     */
    private $all = [];

    /**
     * ParseTgLogCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(?string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $formatted = [];
        $iteration = $this->readLog('var/log/tg_bot.log');
        foreach ($iteration as $line) {
            $result = $this->lineHandler($line);

            if ($result !== false) {
                $formatted[] = $f = $this->handleExtractedData($result);
                echo $f . "\n";
            }
        }

//        echo implode("\n", $formatted);


        $hard = [];
        $sum = [
            'all' => 0,
            'double' => 0,
        ];
        foreach ($this->all as $id => $item) {
            $sum['all'] += $item;
            if (isset($this->summary[$id])) {
                $hard[$id] = $item - $this->summary[$id];
            }
        }

        foreach ($this->summary as $item) {
            $sum['double'] += $item;
        }

        dd($this->summary, $this->all, array_diff_key($this->all, $this->summary), $hard, $sum);

        $io->success('Success.');

        return Command::SUCCESS;
    }

    protected function handleExtractedData($result)
    {
        $to = $this->entityManager->getRepository(User::class)->findOneBy([
            'telegramId' => $result['telegram_id'],
        ]);

        isset($this->all[$to->getId()]) ? $this->all[$to->getId()]++ : $this->all[$to->getId()] = 1;

        $criteria = new Criteria();
        $criteria->orWhere($criteria->expr()->in('email', $result['emails']))
            ->orWhere($criteria->expr()->in('emailAlt', $result['emails']));

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->matching($criteria);

        $userIds = $users->map(function (User $user) {
            return $user->getId();
        })->toArray();

        $userIds = array_values($userIds);
        sort($userIds);

        if (isset($userIds[0], $userIds[1])) {
            if (isset($this->log[$userIds[0]][$userIds[1]])) {
                /** @var User $u */
                $u = $this->log[$userIds[0]][$userIds[1]];
                isset($this->summary[$u->getId()]) ? $this->summary[$u->getId()]++ : $this->summary[$u->getId()] = 2;
            }
            $this->log[$userIds[0]][$userIds[1]] = $to;
        } else {
            echo "!!!!!!! " . print_r($userIds, true);
        }

        $formattedUsers = $users->map(function (User $user) {
            return $user->getFullName() . '<' . $user->getActualEmail() . '>';
        });

        return $to->getFullName() . '<' . $to->getActualEmail() . '>: ' . implode(',', $formattedUsers->toArray());
    }

    protected function lineHandler($line)
    {
        preg_match('/[a-z0-9_\-\:\+\.]+/i', $line, $date);
        if (!isset($date[0]) || (new \DateTime($date[0]))->format('Y-m-d') != '2021-04-12') {
            return false;
        }

        preg_match('/tgbot\.INFO\:\sSEND\s(.*)\[\]/i', $line, $message);
        if (empty($message)) {
            return false;
        }

        $message = trim($message[1]);
        $message = json_decode($message);

        if (!isset($message->text)) {
            return false;
        }

        preg_match_all('/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i', $line, $emails);
        if (empty($emails[0])) {
            return false;
        }

        return [
            'telegram_id' => $message->chatId,
            'emails' => $emails[0],
        ];
    }

    /**
     * @param $path
     * @return \Generator
     */
    protected function readLog($path)
    {
        $handle = fopen($path, "r");

        while (!feof($handle)) {
            yield trim(fgets($handle));
        }

        fclose($handle);
    }
}
