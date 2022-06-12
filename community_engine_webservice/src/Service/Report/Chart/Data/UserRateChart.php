<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Chart\Data;


use App\Entity\Community;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\Report\Chart\AbstractChart;
use App\Service\Report\Chart\ChartDataset;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Class UserRegistrationChart
 * @package App\Service\Report\Chart\Data
 */
class UserRateChart extends AbstractChart
{
    const TYPE_AVG_DATE = 1;
    const TYPE_AVG_SUMMARY = 2;
    const TYPE_AVG_SUMMARY_LINE = 3;

    /**
     * @var array
     */
    private $labels = [];
    /**
     * @var array
     */
    private $datasets = [];

    /**
     * @var Community
     */
    private $community;

    /**
     * @var array
     */
    private $types;
    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    /**
     * UserRegistrationChart constructor.
     * @param ChartBuilderInterface $chartBuilder
     * @param ReviewRepository $reviewRepository
     */
    public function __construct(
        ChartBuilderInterface $chartBuilder,
        ReviewRepository $reviewRepository
    )
    {
        parent::__construct($chartBuilder);
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    /**
     * @param Community $community
     * @return UserRateChart
     */
    public function setCommunity(Community $community): UserRateChart
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @param array $types
     * @return UserRateChart
     */
    public function setTypes(array $types): UserRateChart
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @return array
     */
    protected function getDatasets(): array
    {
        return array_values(
            array_intersect_key($this->datasets, array_flip($this->types))
        );
    }

    /**
     * @return array
     */
    protected function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return mixed
     */
    protected function handle()
    {
        if (!empty($this->datasets)) {
            return;
        }

        $avg = null;
        $avgChart = [];
        $data = [];
        $rows = $this->reviewRepository->findAllByCommunityGroupByCreatedAt($this->community);
        foreach ($rows as &$row) {
            if (is_null($row['avg'])) {
                continue;
            }
            $this->labels[] = (new \DateTime($row['created']))->format('Y-m-d');
            $row['avg'] = round($row['avg'], 2);
            $data[] = $row['avg'];
            if (is_null($avg)) {
                $avg = $row['avg'];
            }
            $avg = ($row['avg'] + $avg) / 2;
            $avgChart[] = round($avg, 2);
        }

        $this->datasets[self::TYPE_AVG_DATE] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' AVG by weeks')
            ->setBorderColor('rgb(255, 99, 132)')
            ->setBackgroundColor('rgb(255, 99, 132)')
            ->setData($data)
            ->__toArray();

        $this->datasets[self::TYPE_AVG_SUMMARY] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' AVG')
            ->setBorderColor('rgb(54, 162, 235)')
            ->setBackgroundColor('rgb(54, 162, 235)')
            ->setData($avgChart)
            ->__toArray();

        $this->datasets[self::TYPE_AVG_SUMMARY_LINE] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' AVG')
            ->setBorderColor('rgb(255, 159, 64)')
            ->setBackgroundColor('rgb(255, 159, 64)')
            ->setData($avgChart)
            ->setType(Chart::TYPE_LINE)
            ->__toArray();
    }
}