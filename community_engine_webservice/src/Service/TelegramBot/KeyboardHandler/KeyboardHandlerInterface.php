<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot\KeyboardHandler;


use TelegramBot\Api\Types\CallbackQuery;

/**
 * Interface KeyboardHandlerInterface
 * @package App\Service\TelegramBot\KeyboardHandler
 */
interface KeyboardHandlerInterface
{
    /**
     * @return string
     */
    public static function getCallbackName(): string;

    /**
     * @param CallbackQuery $query
     */
    public function handle(CallbackQuery $query): void;
}