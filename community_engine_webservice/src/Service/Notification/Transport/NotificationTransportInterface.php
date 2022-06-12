<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Notification\Transport;

use App\Entity\Community;
use App\Entity\Notification\NotificationTransport;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;


/**
 * Interface NotificationTransportServiceInterface
 * @package App\Service\Notification
 */
interface NotificationTransportInterface
{
    /**
     * @param NotificationTransport $transport
     * @param Community|null $community
     * @param Collection|User[] $users
     * @param array $meta
     * @param bool $deferred
     * @return void
     */
    public function send(
        NotificationTransport $transport,
        ?Community $community,
        Collection $users,
        array $meta,
        bool $deferred
    ): void;
}