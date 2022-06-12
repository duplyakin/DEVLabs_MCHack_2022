<?php

namespace App\EventSubscriber;

use App\Service\CommunityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Class LogoutSubscriber
 * @package App\EventSubscriber
 */
class LogoutSubscriber implements EventSubscriberInterface
{
    /**
     * @var CommunityService
     */
    private $communityService;

    /**
     * LogoutSubscriber constructor.
     * @param CommunityService $communityService
     */
    public function __construct(CommunityService $communityService)
    {
        $this->communityService = $communityService;
    }

    /**
     * @param LogoutEvent $event
     */
    public function onLogoutEvent(LogoutEvent $event)
    {
        $event->setResponse(
            $this->communityService->clearCurrentCommunity(
                $event->getResponse()
            )
        );
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
