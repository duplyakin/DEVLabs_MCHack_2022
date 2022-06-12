<?php

namespace App\MessageHandler;

use App\Message\CreateTelegramGroupMessage;
use danog\MadelineProto\API;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Twig\Environment;

/**
 * Class CreateTelegramGroupMessageHandler
 * @package App\MessageHandler
 * @deprecated
 */
final class CreateTelegramGroupMessageHandler implements MessageHandlerInterface
{
    /**
     * @var \danog\MadelineProto\API
     */
    protected $madelineProto;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $users = [];

    /**
     * SendTelegramMessageHandler constructor.
     * @param API $madelineProto
     * @param Environment $twig
     */
    public function __construct(API $madelineProto, Environment $twig)
    {
        $this->madelineProto = $madelineProto;
        $this->madelineProto->start();
        $this->twig = $twig;
    }

    /**
     * @param CreateTelegramGroupMessage $message
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(CreateTelegramGroupMessage $message)
    {
        $users = $this->getUsers($message);
        if (count($users) < 2) {
            return;
        }
        usleep(100 * 1000);

        
        $channel = $this->madelineProto->channels->createChannel([
            'broadcast' => false,
            'megagroup' => true,
            'title' => $message->getTitle(),
            'about' => $message->getAbout(),
        ]);

        if (!isset($channel['updates'][1]['channel_id'])) {
            return;
        }

        $this->channel = 'channel#' . $channel['updates'][1]['channel_id'];

        usleep(800 * 1000);

        $this->madelineProto->channels->inviteToChannel([
            'channel' => $this->channel,
            'users' => $users,
        ]);

        $this->madelineProto->messages->sendMessage([
            'peer' => $this->channel,
            'message' => $this->getInitialMessage($message),
            'parse_mode' => 'html',
            'no_webpage' => true,
        ]);

        //$this->madelineProto->channels->leaveChannel(['channel' => $this->channel]);
    }

    /**
     * @param CreateTelegramGroupMessage $message
     * @return array
     */
    protected function getUsers(CreateTelegramGroupMessage $message): array
    {
        $users = [];
        $this->users = [];
        foreach ($message->getCall()->getUsers() as $userCall) {
            $username = $userCall->getUser()->getTelegramUsername();
            if (!$username) {
                continue;
            }
            $this->users[] = $userCall->getUser();
            $users[] = $username;
        }
        if (!empty($users)) {
            try {
                $this->madelineProto->users->getUsers(['id' => $users]);
            } catch (\Exception $e) {
                $users = [];
            }
        }
        return $users;
    }

    /**
     * @param CreateTelegramGroupMessage $message
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getInitialMessage(CreateTelegramGroupMessage $message)
    {
        return $this->twig->render('telegram/call.html.twig', array_merge([
            'users' => $this->users,
            'call' => $message->getCall(),
        ], $message->getParams()));
    }
}
