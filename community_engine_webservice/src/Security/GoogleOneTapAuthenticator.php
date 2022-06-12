<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;


use App\Entity\User;
use App\Service\FileUploaderService;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class GoogleOneTapAuthenticator
 * @package App\Security
 */
class GoogleOneTapAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
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
     * GoogleOneTapAuthenticator constructor.
     * @param EntityManagerInterface $em
     * @param FileUploaderService $fileUploaderService
     * @param ProfileService $profileService
     * @param RequestStack $request
     */
    public function __construct(
        EntityManagerInterface $em,
        FileUploaderService $fileUploaderService,
        ProfileService $profileService,
        RequestStack $request
    )
    {
        $this->em = $em;
        $this->fileUploaderService = $fileUploaderService;
        $this->profileService = $profileService;
        $this->request = $request;
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *
     * - For a form login, you might redirect to the login page
     *
     *     return new RedirectResponse('/login');
     *
     * - For an API token authentication system, you return a 401 response
     *
     *     return new Response('Auth header required', 401);
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'onetap_google_check';
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      return [
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      ];
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     *
     * @return mixed Any non-null value
     *
     * @throws \UnexpectedValueException If null is returned
     */
    public function getCredentials(Request $request)
    {
        $client = new Google_Client(['client_id' => $_ENV['OAUTH_GOOGLE_CLIENT_ID']]);
        $payload = $client->verifyIdToken($request->get('credential'));
        if ($payload && isset($payload['email_verified']) && $payload['email_verified']) {
            return [
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'picture' => $payload['picture'] ?? null,
                'firstName' => $payload['given_name'] ?? null,
                'lastName' => $payload['family_name'] ?? null,
                'refresh_token' => $payload['refresh_token'] ?? null,
            ];
        } else {
            throw new \UnexpectedValueException();
        }
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     *
     * @throws AuthenticationException
     *
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var User $existingUser */
        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['googleId' => $credentials['id']]);
        if ($existingUser) {
            //TODO
            if (!empty($credentials['refresh_token']) && empty($existingUser->getGoogleRefreshToken())) {
                $existingUser->setGoogleRefreshToken($credentials->getRefreshToken());
                $this->em->persist($existingUser);
                $this->em->flush();
            }
            return $existingUser;
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            $user = new User();
            $user->setEmail($credentials['email']);
            $user->setPassword(uniqid());
            $user->setFirstName($credentials['firstName']);
            $user->setLastName($credentials['lastName']);
            $user->setGoogleId($credentials['id']);
            $this->profileService->setInvitedBy($user, $this->request->getCurrentRequest());
            if ($credentials['picture']) {
                $user->setPicture(
                    $this->fileUploaderService->uploadFromUrl($credentials['picture'])
                );
            }
            $this->em->persist($user);
            $this->em->flush();
        }

        //TODO
        if (!empty($credentials['refresh_token']) && empty($user->getGoogleRefreshToken())) {
            $user->setGoogleRefreshToken($credentials->getRefreshToken());
            $this->em->persist($user);
            $this->em->flush();
        }
        return $user;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If false is returned, authentication will fail. You may also throw
     * an AuthenticationException if you wish to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 401 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return $this->profileService->redirect($token->getUser());
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        return true;
    }
}