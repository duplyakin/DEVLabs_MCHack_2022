<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\Common\Collections;


class ArrayCollection extends \Doctrine\Common\Collections\ArrayCollection
{
    /**
     * @return string
     */
    public function __toString()
    {
        return implode(', ', $this->toArray());
    }
}