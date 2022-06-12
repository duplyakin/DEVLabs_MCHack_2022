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


class ReadyPAKeyboard implements KeyboardInterface
{
    /**
     * @param null $param
     * @return array
     */
    public static function getButtons($param = null): array
    {
        $param = (array)$param;
        $url = !isset($param['pa_url']) ? 'https://www.meetsup.co/user/communities' : $param['pa_url'];
        return [
            [
                [
                    'text' => 'Подтвердить участие',
                    'url' => $url,
                ]
            ]
        ];
    }
}