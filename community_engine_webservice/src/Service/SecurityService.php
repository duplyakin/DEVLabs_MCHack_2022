<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;


/**
 * Class SecurityService
 * @package App\Service
 */
class SecurityService
{
    /**
     *
     */
    const SALT = '&@7B&&rj@cW5&3Khb*ZCPtBWtk9pBQNG57zk2L%B-Kkp$&jL6rZ$8FU_zC2tbLTT';
    const DELIMITER = '_';

    /**
     * @param string $data
     * @return string
     */
    public function createToken(string $data)
    {
        return $data . self::DELIMITER . crc32($data . self::SALT);
    }

    /**
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token)
    {
        $token = explode(self::DELIMITER, $token);
        if (!isset($token[0], $token[1])) {
            return false;
        }
        return $token[1] == crc32($token[0] . self::SALT);
    }

    /**
     * @param $token
     * @return null|string
     */
    public function getData($token)
    {
        if (!$this->validateToken($token)) {
            return null;
        }

        return explode(self::DELIMITER, $token)[0];
    }
}