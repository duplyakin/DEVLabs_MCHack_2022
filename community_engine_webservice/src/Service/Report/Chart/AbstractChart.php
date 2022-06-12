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


use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Class AbstractChart
 * @package App\Service\Report\Chart
 */
abstract class AbstractChart
{
    /**
     * @var ChartBuilderInterface
     */
    private $chartBuilder;

    /**
     * AbstractChart constructor.
     * @param ChartBuilderInterface $chartBuilder
     */
    public function __construct(ChartBuilderInterface $chartBuilder)
    {
        $this->chartBuilder = $chartBuilder;
    }

    /**
     * @return array
     */
    abstract protected function getDatasets(): array;

    /**
     * @return array
     */
    abstract protected function getLabels(): array;

    /**
     * @return mixed
     */
    abstract protected function handle();

    /**
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * @param string $type
     * @return Chart
     */
    public function getChart(string $type = Chart::TYPE_LINE)
    {
        $this->handle();

        return $this->chartBuilder->createChart($type)
            ->setData([
                    'labels' => $this->getLabels(),
                    'datasets' => $this->getDatasets(),
                ]
            );

    }


}