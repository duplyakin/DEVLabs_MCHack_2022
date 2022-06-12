<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class SendEmailMessageHandler
 * @package App\MessageHandler
 */
final class SendEmailMessageHandler implements MessageHandlerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var LoggerInterface
     */
    private $emailLogger;

    /**
     * SendEmailMessageHandler constructor.
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $parameterBag
     * @param LoggerInterface $emailLogger
     */
    public function __construct(
        MailerInterface $mailer,
        ParameterBagInterface $parameterBag,
        LoggerInterface $emailLogger
    )
    {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
        $this->emailLogger = $emailLogger;
    }

    /**
     * @param SendEmailMessage $message
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function __invoke(SendEmailMessage $message)
    {
        if (empty($message->getTo())) {
            return;
        }

        try {
            $from = $message->getFrom() ?? new Address($this->parameterBag->get('mail_from'), 'Meetsup');
            $reply = $message->getReplyTo() ?? new Address($this->parameterBag->get('mail_support'), 'Meetsup');

            $email = (new Email())
                ->from($from)
                ->replyTo($reply)
                ->to($message->getTo())
                ->subject($message->getSubject())
                ->html($message->getBody());

            if ($message->getAttach()) {
                $email->attach($message->getAttach(), 'invite.ics', 'text/calendar');
            }

            $this->emailLogger->info('SENT [' . $message->getTo() . '][' . $message->getSubject() . ']');
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->emailLogger->error($e->getMessage());
        }
    }
}
