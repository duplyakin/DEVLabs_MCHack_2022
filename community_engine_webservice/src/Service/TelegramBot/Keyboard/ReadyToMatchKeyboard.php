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


use App\Service\TelegramBot\KeyboardHandler\ReadyToMatchKeyboardHandler;

class ReadyToMatchKeyboard implements KeyboardInterface
{
    /**
     * @param null $param
     * @return array
     */
    public static function getButtons($param = null): array
    {
        return [
            [
                [
                    'text' => 'Подтверждаю участие',
                    'callback_data' => ReadyToMatchKeyboardHandler::getCallbackName()
                ]
            ]
        ];
    }
}