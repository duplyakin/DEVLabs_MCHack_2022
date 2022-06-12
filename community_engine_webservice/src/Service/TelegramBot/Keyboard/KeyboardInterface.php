<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot\Keyboard;


/**
 * Interface KeyboardInterface
 * @package App\Service\TelegramBot\Keyboard
 */
interface KeyboardInterface
{
    /**
     * @param null $param
     * @return array
     */
    public static function getButtons($param = null): array;
}