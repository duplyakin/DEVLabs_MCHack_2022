<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class ResponseSubscriber
 * @package App\EventSubscriber
 */
class ResponseSubscriber implements EventSubscriberInterface
{
    const GDPR_COOKIE_NAME = 'cp-cookies-accepted';

    /**
     * @var string[]
     */
    private $whitelist = [
        'PHPSESSID',
    ];

    /**
     * @param string[] $whitelist
     */
    public function __construct(array $whitelist = [])
    {
        $this->whitelist = $whitelist;
    }

    /**
     * @param ResponseEvent $event
     */
    public function onResponseEvent(ResponseEvent $event)
    {
        $event->getResponse()
            ->setExpires(new \DateTime())
            ->setMaxAge(0);

        $this->cleanCookies($event);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ResponseEvent::class => 'onResponseEvent',
        ];
    }

    /**
     * @param ResponseEvent $event
     */
    public function cleanCookies(ResponseEvent $event): void
    {
        $headers = $event->getResponse()->headers;
        $cookies = $headers->getCookies();

        if ($this->hasCookieConsent($cookies)) {
            return;
        }

        foreach ($cookies as $cookie) {
            if ($this->isWhitelisted($cookie)) {
                continue;
            }

            $headers->removeCookie($cookie->getName());
        }
    }

    /**
     * @param Cookie[] $cookies
     * @return bool
     */
    private function hasCookieConsent(array $cookies): bool
    {
        foreach ($cookies as $cookie) {
            if (self::GDPR_COOKIE_NAME === $cookie->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Cookie $cookie
     * @return bool
     */
    private function isWhitelisted(Cookie $cookie): bool
    {
        foreach ($this->whitelist as $name) {
            if ($cookie->getName() === $name || 1 === preg_match('#' . $name . '#', $cookie->getName())) {
                return true;
            }
        }

        return false;
    }
}
