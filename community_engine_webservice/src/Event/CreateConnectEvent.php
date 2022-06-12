<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;


use App\Entity\Call;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CreateConnectEvent
 * @package App\Event
 */
class CreateConnectEvent extends Event
{
    /**
     * @var Call
     */
    private $connect;

    /**
     * CreateConnectEvent constructor.
     * @param Call $connect
     */
    public function __construct(Call $connect)
    {
        $this->connect = $connect;
    }

    /**
     * @return Call
     */
    public function getConnect(): Call
    {
        return $this->connect;
    }
}