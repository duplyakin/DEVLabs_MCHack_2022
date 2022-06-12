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
use App\Entity\Notification\Notification;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class NotificationTransportService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * NotificationTransportService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Notification $notificationEntity
     * @param $users
     * @param $meta
     * @param Community|null $community
     * @param bool $deferred
     */
    public function handle(
        Notification $notificationEntity,
        $users,
        $meta,
        ?Community $community,
        bool $deferred
    )
    {
        foreach ($notificationEntity->getNotificationTransports() as $transport) {
            if (!$this->container->has($transport->getNodeValue())) {
                //TODO LOGS
                continue;
            }
            /** @var NotificationTransportInterface $transportObject */
            $transportObject = $this->container->get($transport->getNodeValue());
            $transportObject->send(
                $transport,
                $community,
                $users,
                $meta,
                $deferred
            );
        }
    }
}