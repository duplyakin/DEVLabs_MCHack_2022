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
use App\Repository\UserRepository;
use App\Service\Report\Chart\AbstractChart;
use App\Service\Report\Chart\ChartDataset;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

/**
 * Class UserRegistrationChart
 * @package App\Service\Report\Chart\Data
 */
class UserRegistrationChart extends AbstractChart
{
    const TYPE_REGISTRATION = 1;
    const TYPE_GROWTH = 2;

    /**
     * @var UserRepository
     */
    private $userRepository;

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
     * UserRegistrationChart constructor.
     * @param ChartBuilderInterface $chartBuilder
     * @param UserRepository $userRepository
     */
    public function __construct(
        ChartBuilderInterface $chartBuilder,
        UserRepository $userRepository
    )
    {
        parent::__construct($chartBuilder);
        $this->userRepository = $userRepository;
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
     * @return $this
     */
    public function setCommunity(Community $community)
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @param array $types
     * @return UserRegistrationChart
     */
    public function setTypes(array $types): UserRegistrationChart
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

        $sum = 0;
        $sumChart = [];
        $rows = $this->userRepository->findAllByCommunityGroupByCreatedAt($this->community);
        foreach ($rows as &$row) {
            $this->labels[] = $row['created'];
            $row = $row['count'];
            $sum += $row;
            $sumChart[] = $sum;
        }
        $this->datasets[self::TYPE_REGISTRATION] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' Registration')
            ->setBorderColor('rgb(255, 99, 32)')
            ->setBackgroundColor('#f5f5f5')
            ->setData($rows)
            ->__toArray();

        $this->datasets[self::TYPE_GROWTH] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' Growth')
            ->setBorderColor('rgb(255, 99, 32)')
            ->setBackgroundColor('#f5f5f5')
            ->setData($sumChart)
            ->__toArray();
    }
}