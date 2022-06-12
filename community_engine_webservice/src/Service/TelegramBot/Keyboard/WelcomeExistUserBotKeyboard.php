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


class WelcomeExistUserBotKeyboard implements KeyboardInterface
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
                    'text' => 'Мой нетворкинг',
                    'url' => 'https://www.meetsup.co/user/network'
                ],
            ],
            [
                [
                    'text' => 'Мой профиль',
                    'url' => 'https://www.meetsup.co/user/profile'
                ],
            ],
            [
                [
                    'text' => 'Оповещения и участие',
                    'url' => 'https://www.meetsup.co/user/profile/notify'
                ]
            ]
        ];
    }
}