<?php

namespace App\EventSubscriber;

use App\Entity\Community;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RequestSubscriber
 * @package App\EventSubscriber
 */
class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * RequestSubscriber constructor.
     * @param SessionInterface $session
     * @param RouterInterface $router
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        SessionInterface $session,
        RouterInterface $router,
        ParameterBagInterface $parameterBag
    )
    {
        $this->session = $session;
        $this->router = $router;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        //TODO Remove www. domain cookie

        $event->getResponse()
            ->headers
            ->clearCookie(
                'g_state',
                '/',
                'www' . $this->parameterBag->get('cookie_domain'),
                true,
                true
            );

        $event->getResponse()
            ->headers
            ->clearCookie(
                'PHPSESSID',
                '/',
                'www' . $this->parameterBag->get('cookie_domain'),
                true,
                true
            );

        ///////////////////////////////////////

        $subdomain = self::getSubdomain($event->getRequest());
        $community = $event->getRequest()->get('community') ?? $subdomain;

        if ($community) {
            $event->getResponse()
                ->headers
                ->setCookie(
                    Cookie::create(
                        Community::COOKIE_KEY,
                        $community,
                        time() + (365 * 24 * 60 * 60),
                        '/',
                        $this->parameterBag->get('cookie_domain')
                    )
                );
        }

        $invite = $event->getRequest()->get('_i');

        if ($invite) {
            $invite = preg_replace("/[^A-Za-z0-9\-\.]/u", "", $invite);
            $event->getResponse()
                ->headers
                ->setCookie(
                    Cookie::create(
                        User::INVITE_COOKIE_KEY,
                        $invite,
                        time() + (365 * 24 * 60 * 60),
                        '/',
                        $this->parameterBag->get('cookie_domain')
                    )
                );
            $event->getResponse()
                ->headers
                ->setCookie(
                    Cookie::create(
                        User::INVITE_COMMUNITY_COOKIE_KEY,
                        $community,
                        time() + (365 * 24 * 60 * 60),
                        '/',
                        $this->parameterBag->get('cookie_domain')
                    )
                );
        }

        if (
            $event->getResponse()->getStatusCode() == 200 &&
            $subdomain &&
            $event->getRequest()->get('_route') != 'landing'
        ) {
            $event->setResponse(
                new RedirectResponse($this->router->generate('index'))
            );
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public static function getSubdomain(Request $request)
    {
        $value = explode('.', $request->getHttpHost());
        $value = reset($value);
        if ($value == 'www' || $value == 'meetsup' || $value == 'dev') {
            return null;
        }

        return $value;
    }
}
