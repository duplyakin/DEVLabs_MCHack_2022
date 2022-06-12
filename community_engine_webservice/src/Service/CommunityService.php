<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;


use App\Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Community;
use App\Entity\User;
use App\EventSubscriber\RequestSubscriber;
use App\Repository\CommunityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommunityService
 * @package App\Service
 */
class CommunityService
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var null|Community
     */
    protected $community;

    /**
     * CommunityService constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    )
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Community|null
     */
    public function getCurrentCommunity(): ?Community
    {
        $community = $this->getCommunityFromSubDomain();
        if ($community) {
            return $community;
        }

        $community = $this->getCommunityFromCookie();
        if ($community) {
            return $community;
        }

        return null;
    }

    /**
     * @return Community|null
     */
    public function getCommunity()
    {
        return $this->getCurrentCommunity() ?? $this->getDefaultCommunity();
    }

    /**
     * @return Community|null
     */
    public function getCommunityFromSubDomain(): ?Community
    {
        $communityName = RequestSubscriber::getSubdomain($this->requestStack->getCurrentRequest());
        if (!$communityName) {
            return null;
        }

        if ($this->community instanceof Community) {
            return $this->community;
        }

        /** @var CommunityRepository $repository */
        $repository = $this->entityManager->getRepository(Community::class);
        /** @var Community $communityEntity */
        $this->community = $repository->findOneBy([
            'url' => $communityName,
        ]);

        return $this->community;
    }

    /**
     * @param null|Response $response
     * @return Response
     */
    public function clearCurrentCommunity(?Response $response = null)
    {
        $response = $response ?? new Response();
        $response->headers->clearCookie(Community::COOKIE_KEY);
        return $response;
    }

    /**
     * @return null|Community
     */
    protected function getDefaultCommunity()
    {
        return $this->entityManager->getRepository(Community::class)
            ->findOneBy([
                'is_default' => 1,
            ]);
    }

    /**
     * @return Community|null
     */
    protected function getCommunityFromCookie(): ?Community
    {
        $communityName = $this->requestStack
            ->getCurrentRequest()
            ->cookies
            ->get(Community::COOKIE_KEY);

        if (!$communityName) {
            return null;
        }

        /** @var CommunityRepository $repository */
        $repository = $this->entityManager->getRepository(Community::class);
        /** @var Community $communityEntity */
        return $repository->findOneBy([
            'url' => $communityName,
        ]);
    }
}