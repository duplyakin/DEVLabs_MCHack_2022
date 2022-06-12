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
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Report\Chart\AbstractChart;
use App\Service\Report\Chart\ChartDataset;
use Doctrine\ORM\Query;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

/**
 * Class UserRegistrationChart
 * @package App\Service\Report\Chart\Data
 */
class UserProfileCompleteChart extends AbstractChart
{
    const TYPE_PROFILE = 1;
    const TYPE_ONBOARDING = 2;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var array
     */
    private $datasets = [];

    /**
     * @var Community
     */
    private $community;

    /**
     * @var integer
     */
    private $type;

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
     * @return UserProfileCompleteChart
     */
    public function setCommunity(Community $community): UserProfileCompleteChart
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @param int $type
     * @return UserProfileCompleteChart
     */
    public function setType(int $type): UserProfileCompleteChart
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    protected function getDatasets(): array
    {
        return [$this->datasets[$this->type]];
    }

    /**
     * @return array
     */
    protected function getLabels(): array
    {
        if ($this->type == self::TYPE_PROFILE) {
            return [
                'Profile is not completed',
                'Profile completed',
            ];
        }

        if ($this->type == self::TYPE_ONBOARDING) {
            return [
                'Onboarding is not completed',
                'Onboarding completed',
            ];
        }
    }

    /**
     * @return mixed
     */
    protected function handle()
    {
        if (!empty($this->datasets)) {
            return;
        }

        $rows = $this->userRepository->createQueryBuilder('u')
            ->select([
                'u.profile_complete',
                's.question_complete',
                's.send_notifications',
            ])
            ->join('u.userCommunitySettings', 's')
            ->andWhere(':community MEMBER OF u.communities')
            ->setParameter(':community', $this->community)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        $all = count($rows);
        $profile = 0;
        $onboarding = 0;
        foreach ($rows as $row) {
            if ($row['profile_complete']) {
                $profile++;
            }

            if ($row['question_complete']) {
                $onboarding++;
            }
        }

        $this->datasets[self::TYPE_PROFILE] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' Profile completed')
            ->setBorderColor('rgb(255, 255, 255)')
            ->setBackgroundColor(['rgb(255, 99, 132)', 'rgb(54, 162, 235)'])
            ->setData([$all - $profile, $profile])
            ->__toArray();

        $this->datasets[self::TYPE_ONBOARDING] = (new ChartDataset())
            ->setLabel($this->community->getTitle() . ' Onboarding completed')
            ->setBorderColor('rgb(255, 255, 255)')
            ->setBackgroundColor(['rgb(255, 99, 132)', 'rgb(54, 162, 235)'])
            ->setData([$all - $onboarding, $onboarding])
            ->__toArray();
    }
}