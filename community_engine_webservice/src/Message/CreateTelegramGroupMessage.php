<?php

namespace App\Message;

use App\Entity\Call;

/**
 * Class CreateTelegramGroupMessage
 * @package App\Message
 * @deprecated
 */
final class CreateTelegramGroupMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    /**
     * @var Call
     */
    private $call;

    /**
     * @var array
     */
    private $params = [];

    /**
     * CreateTelegramGroupMessage constructor.
     * @param Call $call
     * @param array $params
     */
    public function __construct(Call $call, array $params = [])
    {
        $this->call = $call;
        $this->params = $params;
    }

    /**
     * @return Call
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Meetsup Network';
    }

    /**
     * @return string
     */
    public function getAbout()
    {
        return 'Meetsup conference';
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
