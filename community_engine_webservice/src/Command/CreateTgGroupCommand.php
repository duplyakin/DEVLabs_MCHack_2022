<?php

namespace App\Command;

use App\Entity\Call;
use App\Repository\CallRepository;
use App\Service\Notification\TelegramNotificationService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateTgGroupCommand
 * @package App\Command
 */
class CreateTgGroupCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CreateTgGroupCommand constructor.
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
    protected static $defaultName = 'app:create-tg-group';

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Tg create group for connects');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dd('unavailable');
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new Question('Please enter Connect From ID: ');
        $from = $helper->ask($input, $output, $question);

        $question = new Question('Please enter Connect To ID: ');
        $to = $helper->ask($input, $output, $question);

        /** @var CallRepository $repository */
        $repository = $this->entityManager->getRepository(Call::class);
        $connects = $repository->findAllRange($from, $to);
        $io->note('Connect counts: ' . count($connects));
        /** @var Call $connect */
        foreach ($connects as $connect) {
            $io->block($connect);
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
