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
use App\Repository\CallRepository;
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
class UserConnectChart extends AbstractChart
{
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
    private $params = [];

    /**
     * @var CallRepository
     */
    private $callRepository;

    /**
     * UserRegistrationChart constructor.
     * @param ChartBuilderInterface $chartBuilder
     * @param CallRepository $callRepository
     */
    public function __construct(
        ChartBuilderInterface $chartBuilder,
        CallRepository $callRepository
    )
    {
        parent::__construct($chartBuilder);
        $this->callRepository = $callRepository;
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
     * @return UserConnectChart
     */
    public function setCommunity(Community $community): UserConnectChart
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    protected function getDatasets(): array
    {
        return $this->datasets;
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

        $this->params['all'] = 0;
        $rows = $this->callRepository->findAllByCommunityGroupByCreatedAt($this->community);
        foreach ($rows as &$row) {
            $this->labels[] = (new \DateTime($row['created']))->format('Y-m-d');
            $row = $row['count'];
            $this->params['all'] += $row;
        }

        $this->datasets[] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' Connects by weeks')
            ->setBorderColor('rgb(255, 99, 132)')
            ->setBackgroundColor('rgb(255, 99, 132)')
            ->setData($rows)
            ->__toArray();
    }
}