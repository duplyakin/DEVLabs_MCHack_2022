<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Call;


use App\Entity\Call;
use App\Entity\CallUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ConnectCallService
 * @package App\Service\Call
 * @deprecated Used only peer-to-peer video connections
 */
class ConnectCallService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ConnectCallService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UserInterface $user
     * @param Call $call
     * @param string $peerId
     * @return bool
     */
    public function savePeerId(UserInterface $user, Call $call, string $peerId): bool
    {
        $callUser = $this->getCallUserInstance($user, $call);
        if (!$callUser) {
            return false;
        }

        $peerId = preg_replace("/[^A-Za-z0-9?!]/", "", $peerId);
        $callUser->setPeerId($peerId);
        $this->entityManager->persist($callUser);
        $this->entityManager->flush();
        return true;
    }

    /**
     * @param UserInterface $user
     * @param Call $call
     * @return string
     */
    public function getSessionId(UserInterface $user, Call $call): string
    {
        return md5($user->getUsername() . $call->getUuid());
    }

    /**
     * @param User $user
     * @param Call $call
     * @return CallUser|null
     */
    protected function getCallUserInstance(User $user, Call $call): ?CallUser
    {

    }
}