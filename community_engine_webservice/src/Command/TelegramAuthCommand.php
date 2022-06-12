<?php

namespace App\Command;

use danog\MadelineProto\API;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TelegramAuthCommand
 * @package App\Command
 */
class TelegramAuthCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:telegram-auth';
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TelegramAuthCommand constructor.
     * @param null|string $name
     * @param ContainerInterface $container
     */
    public function __construct(?string $name = null, ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Telegram login');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $madeline = $this->container->get(API::class);
        $madeline->start();
        $madeline->setNoop();
        return Command::SUCCESS;
    }
}
