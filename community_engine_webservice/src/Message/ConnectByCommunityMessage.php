<?php

namespace App\Message;

use App\Entity\Community;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ConnectByCommunityMessage
 * @package App\Message
 */
final class ConnectByCommunityMessage
{
    /**
     * @var Community
     */
    private $community;

    /**
     * ConnectByCommunityMessage constructor.
     * @param Community $community
     */
    public function __construct(Community $community)
    {
        $this->community = $community;
    }

    /**
     * @return Community
     */
    public function getCommunity(): Community
    {
        return $this->community;
    }
}
