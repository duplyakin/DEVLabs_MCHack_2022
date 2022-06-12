<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Chart;


/**
 * Class ChartItem
 * @package App\Service\Report\Chart
 */
/**
 * Class ChartDataset
 * @package App\Service\Report\Chart
 */
class ChartDataset
{
    /**
     * @var
     */
    protected $label;
    /**
     * @var
     */
    protected $backgroundColor;
    /**
     * @var
     */
    protected $borderColor;
    /**
     * @var
     */
    protected $data;
    /**
     * @var
     */
    protected $fill = false;
    /**
     * @var
     */
    protected $cubicInterpolationMode = 'none';
    /**
     * @var
     */
    protected $tension = 0.4;
    /**
     * @var
     */
    protected $type;

    /**
     * @var int
     */
    protected $radius = 3;

    /**
     * @return mixed
     */
    public function getFill()
    {
        return $this->fill;
    }

    /**
     * @param mixed $fill
     * @return ChartDataset
     */
    public function setFill($fill)
    {
        $this->fill = $fill;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCubicInterpolationMode()
    {
        return $this->cubicInterpolationMode;
    }

    /**
     * @param mixed $cubicInterpolationMode
     * @return ChartDataset
     */
    public function setCubicInterpolationMode($cubicInterpolationMode)
    {
        $this->cubicInterpolationMode = $cubicInterpolationMode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTension()
    {
        return $this->tension;
    }

    /**
     * @param mixed $tension
     * @return ChartDataset
     */
    public function setTension($tension)
    {
        $this->tension = $tension;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return ChartDataset
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param mixed $backgroundColor
     * @return ChartDataset
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * @param mixed $borderColor
     * @return ChartDataset
     */
    public function setBorderColor($borderColor)
    {
        $this->borderColor = $borderColor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ChartDataset
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }

    /**
     * @param mixed $type
     * @return ChartDataset
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}