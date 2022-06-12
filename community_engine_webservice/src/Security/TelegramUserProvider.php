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
use App\Service\CommunityService;
use App\Service\FileUploaderService;
use App\Service\ProfileService;
use App\Service\SecurityService;
use BoShurik\TelegramBotBundle\Guard\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


/**
 * Class TelegramUserProvider
 * @package App\Security
 */
class TelegramUserProvider implements UserLoaderInterface, UserFactoryInterface
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RequestStack
     */
    private $request;
    /**
     * @var FileUploaderService
     */
    private $fileUploaderService;
    /**
     * @var ProfileService
     */
    private $profileService;

    /**
     * TelegramUserProvider constructor.
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $request
     * @param FileUploaderService $fileUploaderService
     * @param ProfileService $profileService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $request,
        FileUploaderService $fileUploaderService,
        ProfileService $profileService
    )
    {
        $this->entityManager = $entityManager;
        $this->request = $request;
        $this->fileUploaderService = $fileUploaderService;
        $this->profileService = $profileService;
    }

    /**
     * @param string $id
     * @return \object|null|UserInterface
     */
    public function loadByTelegramId(string $id): ?UserInterface
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['telegramId' => $id]);

        if ($user) {
            $this->setTargetPath($user);
        }

        return $user;
    }

    /**
     * @param array $data
     * @return UserInterface
     */
    public function createFromTelegram(array $data): UserInterface
    {
        /** @var User $existingUser */
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['telegramId' => $data['id']]);
        if ($existingUser) {
            $this->setTargetPath($existingUser);
            return $existingUser;
        }
        $username = $data['username'] ?? null;
        $photo = $data['photo_url'] ?? null;
        $user = null;

//        $token = $this->request->getCurrentRequest()->get('_token');
//        $userId = $this->request->getCurrentRequest()->get('_id');
//
//        if ($userId) {
//            /** @var User $user */
//            $user = $this->entityManager
//                ->getRepository(User::class)
//                ->findOneBy(['publicId' => $userId]);
//        }
//
//        if ($user && $token && $this->securityService->validateToken($token)) {
//            $user->setTelegramId($data['id']);
//            $user->setTelegramUsername($username);
//            $this->entityManager->persist($user);
//            $this->entityManager->flush();
//
//            $returnPath = $this->router->generate('user_profile_notify', [
//                'telegram_connect' => 1,
//            ]);

//
//            $event = new ConnectTelegramExistingUserEvent($user);
//            $this->eventDispatcher->dispatch($event);
//
//            return $user;
//        }

        if ($username) {
            /** @var User $user */
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['telegramUsername' => '@' . $username]);
            if ($user) {
                $this->setTargetPath($user);
                return $user;
            }
//            if ($user && empty($user->getTelegramId())) {
//                $user->setTelegramId($data['id']);
//                $user->setTelegramUsername($username);
//                $this->entityManager->persist($user);
//                $this->entityManager->flush();
//
//                return $user;
//            }
        }

        if (!$user) {
            $user = new User();
            $user->setTelegramUsername($username);
            $user->setPassword(uniqid());
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $user->setTelegramId($data['id']);
            $this->profileService->setInvitedBy($user, $this->request->getCurrentRequest());
            if ($photo) {
                $user->setPicture(
                    $this->fileUploaderService->uploadFromUrl($photo)
                );
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $this->setTargetPath($user);
        return $user;
    }

    /**
     * @param User $user
     */
    protected function setTargetPath(User $user) {
        $response = $this->profileService->redirect($user);
        $this->saveTargetPath(
            $this->request->getCurrentRequest()->getSession(),
            'main',
            $response->getTargetUrl()
        );
    }
}