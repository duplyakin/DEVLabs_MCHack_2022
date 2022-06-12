<?php

namespace App\Security;

use App\Entity\User;
use App\Service\CommunityService;
use App\Service\FileUploaderService;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class FacebookAuthenticator
 * @package App\Security
 */
class FacebookAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FileUploaderService
     */
    private $fileUploaderService;

    /**
     * @var ProfileService
     */
    private $profileService;

    /**
     * @var RequestStack
     */
    private $request;
    /**
     * @var CommunityService
     */
    private $communityService;

    /**
     * FacebookAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param FileUploaderService $fileUploaderService
     * @param ProfileService $profileService
     * @param RequestStack $request
     * @param CommunityService $communityService
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        FileUploaderService $fileUploaderService,
        ProfileService $profileService,
        RequestStack $request,
        CommunityService $communityService
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->profileService = $profileService;
        $this->request = $request;
        $this->em = $em;
        $this->router = $router;
        $this->fileUploaderService = $fileUploaderService;
        $this->communityService = $communityService;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_facebook_check';
    }

    /**
     * @param Request $request
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true

        // For Symfony lower than 3.4 the supports method need to be called manually here:
        // if (!$this->supports($request)) {
        //     return null;
        // }

        return $this->fetchAccessToken($this->getFacebookClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var FacebookUser $facebookUser */
        $facebookUser = $this->getFacebookClient()
            ->fetchUserFromToken($credentials);

        $email = $facebookUser->getEmail();

        /** @var User $existingUser */
        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['facebookId' => $facebookUser->getId()]);
        if ($existingUser) {
            //TODO
            if (!empty($credentials->getRefreshToken()) && empty($existingUser->getFacebookRefreshToken())) {
                $existingUser->setFacebookRefreshToken($credentials->getRefreshToken());
                $this->em->persist($existingUser);
                $this->em->flush();
            }
            return $existingUser;
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(uniqid());
            $user->setFacebookLink($facebookUser->getLink());
            $user->setFirstName($facebookUser->getFirstName());
            $user->setLastName($facebookUser->getLastName());
            $user->setFacebookId($facebookUser->getId());
            $this->profileService->setInvitedBy($user, $this->request->getCurrentRequest());
            if ($facebookUser->getPictureUrl()) {
                $user->setPicture(
                    $this->fileUploaderService->uploadFromUrl($facebookUser->getPictureUrl())
                );
            }
            $this->em->persist($user);
            $this->em->flush();
        }
        //TODO
        if (!empty($credentials->getRefreshToken()) && empty($user->getFacebookRefreshToken())) {
            $user->setFacebookRefreshToken($credentials->getRefreshToken());
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return \KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface
     */
    private function getFacebookClient()
    {
        return $this->clientRegistry
            // "facebook_main" is the key used in config/packages/knpu_oauth2_client.yaml
            ->getClient('facebook_main');
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->profileService->redirect($token->getUser());

//        $community = $this->communityService->getCurrentCommunity();
//        /** @var User $user */
//        $user = $token->getUser();
//        if ($community && !$user->getCommunities()->contains($community)) {
//            $user->addCommunity($community);
//            $this->em->persist($user);
//            $this->em->flush();
//
//        }
//
//        if ($token->getUser()->getQuestionComplete() && $targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
//            return new RedirectResponse($targetPath);
//        }
//        $route = $token->getUser()->getQuestionComplete() ? 'user_profile' : 'user_profile_fill';
//        $targetUrl = $this->router->generate($route);
//
//        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
