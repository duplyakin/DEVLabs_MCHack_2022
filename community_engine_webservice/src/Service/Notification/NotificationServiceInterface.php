<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Notification;


use App\Entity\Call;
use App\Entity\User;
use App\Event\NotificationEvent;

/**
 * Interface NotificationServiceInterface
 * @package App\Service\Notification
 */
interface NotificationServiceInterface
{
    /**
     * @param NotificationEvent $event
     * @return void
     */
    public function handle(NotificationEvent $event): void;
}