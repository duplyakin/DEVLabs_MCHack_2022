<?php

namespace App\Controller;

use App\Entity\Community;
use App\Entity\User;
use App\EventSubscriber\RequestSubscriber;
use App\Form\ContactFormType;
use App\Message\SendEmailMessage;
use App\Repository\CommunityRepository;
use App\Repository\UserRepository;
use App\Security\TokenAuthenticator;
use App\Service\CommunityService;
use App\Service\ProfileService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController extends AbstractController
{
    /**
     * @var CommunityService
     */
    private $communityService;

    /**
     * DefaultController constructor.
     * @param CommunityService $communityService
     */
    public function __construct(CommunityService $communityService)
    {
        $this->communityService = $communityService;
    }

    /**
     * @Route("/i/{id}", name="invite")
     * @param string $id
     * @return RedirectResponse
     */
    public function invite(string $id)
    {
        $response = $this->redirectToRoute('index');
        $id = preg_replace("/[^A-Za-z0-9\-\.]/u", "", $id);
        $response->headers
            ->setCookie(Cookie::create(User::INVITE_COOKIE_KEY, $id));
        return $response;

    }

    /**
     * @Route("/contact", name="contact")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function contact(Request $request)
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contactFormData = $form->getData();
            $this->dispatchMessage(new SendEmailMessage(
                $contactFormData['subject'],
                'support@meetsup.co',
                $this->renderView('emails/contact_form.html.twig', [
                    'message' => $contactFormData['message'],
                    'email' => $contactFormData['email'],
                    'name' => $contactFormData['name'],
                ]),
                new Address($contactFormData['email'], $contactFormData['name'])
            ));
            return $this->json([
                'status' => 'success',
            ]);
        }
        return $this->json([
            'status' => 'error',
            'statusText' => (string)$form->getErrors(),
        ]);
    }

    /**
     * @Route(
     *     "/",
     *     name="landing",
     *     host="{subdomain}.{host}",
     *     requirements={
     *          "subdomain": "^((?!www).)*$",
     *          "host": "meetsup..*|dev.meetsup..*",
     *     }
     * )
     * @param Request $request
     * @param RouterInterface $router
     * @return Response
     */
    public function landing(Request $request, RouterInterface $router)
    {
        $currentCommunity = $this->communityService->getCurrentCommunity();
        if ($currentCommunity && RequestSubscriber::getSubdomain($request)) {
            if ($currentCommunity->getIsDefault()) {
                return $this->redirect($this->getParameter('default_uri'));
            }
            $router->getContext()->setHost($this->getParameter('default_host'));
            $locale = $request->getDefaultLocale();
            return $this->render('default/' . $locale . '_community_landing.html.twig', [
                'community' => $currentCommunity,
            ]);
        }
        throw new NotFoundHttpException();
    }


    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $locale = $request->getDefaultLocale();

        if ($locale == 'en') {
            return $this->render('default/en_b2b.html.twig');
        }
        return $this->render('default/' . $locale . '_index.html.twig');
    }

    /**
     * @Route("/connect", name="app_login")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function connect(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return ($community = $request->get('community')) ?
                $this->redirectToRoute('user_profile_questions', ['community' => $community]) :
                $this->redirectToRoute('user_communities');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('default/connect_v2.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/privacy-policy", name="app_privacy_policy")
     * @param Request $request
     * @return Response
     */
    public function privacyPolicy(Request $request)
    {
        $locale = $request->getDefaultLocale();
        return $this->render('default/' . $locale . '_privacy_policy.html.twig');
    }

    /**
     * @Route("/cookie-policy", name="app_cookie_policy")
     * @param Request $request
     * @return Response
     */
    public function cookiePolicy(Request $request)
    {
        $locale = $request->getDefaultLocale();
        return $this->render('default/' . $locale . '_cookie_policy.html.twig');
    }

    /**
     * @Route("/communities", name="b2b_page")
     * @param Request $request
     * @return Response
     */
    public function b2b(Request $request)
    {
        $locale = $request->getDefaultLocale();
        return $this->render('default/' . $locale . '_b2b.html.twig');
    }

    /**
     * @Route("/_ra", name="redirect_auth")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param CommunityRepository $communityRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @return Response
     */
    public function ra(
        Request $request,
        UserRepository $userRepository,
        CommunityRepository $communityRepository,
        UrlGeneratorInterface $urlGenerator
    )
    {
        if (strstr($request->headers->get('User-Agent'), 'TelegramBot')) {
            return $this->redirectToRoute('index');
        }

        $communityUrl = $request->get('community');
        $token = $request->get('s');

        $user = $userRepository->findOneBy([
            'temp_token' => $token,
        ]);

        if (!$user) {
            return $this->redirectToRoute('index');
        }

        $community = $communityRepository->findOneBy([
            'url' => $communityUrl,
        ]);

        $url = $community ? $urlGenerator->generate('user_profile_questions', [
            'community' => $community->getUrl(),
            '_s' => $token,
        ]) : $urlGenerator->generate('user_communities', [
            '_s' => $token,
        ]);

        return $this->render('redirect.html.twig', [
            'url' => $url,
        ]);
    }

    /**
     * @Route("/unsubscribe/{public_id}/{community_url?}", name="app_unsubscribe")
     * @ParamConverter("user", options={"mapping": {"public_id": "publicId"}})
     * @ParamConverter("community", options={"mapping": {"community_url": "url"}})
     * @param User $user
     * @param Community|null $community
     * @param Request $request
     * @param ProfileService $profileService
     * @param UrlGeneratorInterface $urlGenerator
     * @return Response
     */
    public function unsubscribe(
        User $user,
        ?Community $community,
        Request $request,
        ProfileService $profileService,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $error = null;
        $submittedToken = $request->get('token');

        if ($submittedToken) {
            if ($this->isCsrfTokenValid('unsubscribe', $submittedToken)) {
                $profileService->unsubscribe($user, $community);
            } else {
                $error = 'Security token is expired. Please, reload page!';
            }
        }

        $link = $urlGenerator->generate('user_profile_notification', [
            TokenAuthenticator::QUERY_KEY => $user->getTempToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('default/unsubscribe.html.twig', [
            'user' => $user,
            'notificationLink' => $link,
            'community' => $community,
            'submitted' => (bool)$submittedToken,
            'error' => $error,
        ]);
    }
}
