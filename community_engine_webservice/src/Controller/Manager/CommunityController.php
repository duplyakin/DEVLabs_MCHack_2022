<?php

namespace App\Controller\Manager;

use App\Entity\Community;
use App\Repository\CommunityRepository;
use App\Repository\UserRepository;
use App\Service\Report\Chart\Data\UserConnectChart;
use App\Service\Report\Chart\Data\UserProfileCompleteChart;
use App\Service\Report\Chart\Data\UserRateChart;
use App\Service\Report\Chart\Data\UserRegistrationChart;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Model\Chart;

class CommunityController extends AbstractController
{

    /**
     * @Route("/manager/community", name="manager_community")
     */
    public function index()
    {
        return $this->render('manager/community/index.html.twig', [

        ]);
    }

    /**
     * @Route("/manager/community/{url}/analytics", name="manager_community_analytics")
     * @ParamConverter("url", options={"mapping": {"url": "url"}})
     * @param Community $community
     * @param UserRegistrationChart $userRegistrationChart
     * @param UserRateChart $userRateChart
     * @param UserProfileCompleteChart $userProfileCompleteChart
     * @param UserConnectChart $userConnectChart
     * @return Response
     */
    public function analytics(
        Community $community,
        UserRegistrationChart $userRegistrationChart,
        UserRateChart $userRateChart,
        UserProfileCompleteChart $userProfileCompleteChart,
        UserConnectChart $userConnectChart
    ): Response
    {
        $chart['Registrations'] = $userRegistrationChart
            ->setCommunity($community)
            ->setTypes([UserRegistrationChart::TYPE_REGISTRATION])
            ->getChart();

        $chart['Growth'] = $userRegistrationChart
            ->setCommunity($community)
            ->setTypes([UserRegistrationChart::TYPE_GROWTH])
            ->getChart();

        $chart['Reviews'] = $userRateChart
            ->setCommunity($community)
            ->setTypes([
                UserRateChart::TYPE_AVG_DATE,
//                UserRateChart::TYPE_AVG_SUMMARY_LINE,
//                UserRateChart::TYPE_AVG_SUMMARY,
            ])
            ->getChart(Chart::TYPE_BAR)
            ->setOptions([
                'scales' => [
                    'yAxes' => [
                        ['ticks' => ['min' => 3, 'max' => 5]],
                    ],
                ],
            ]);

        $chart['Connects'] = $userConnectChart
            ->setCommunity($community)
            ->getChart(Chart::TYPE_BAR)
            ->setOptions([
                'scales' => [
                    'yAxes' => [
                        ['ticks' => ['min' => 0]],
                    ],
                ],
            ]);

        $chart['Profile Filled'] = $userProfileCompleteChart
            ->setCommunity($community)
            ->setType(UserProfileCompleteChart::TYPE_PROFILE)
            ->getChart(Chart::TYPE_PIE);

        $chart['Onboarding Filled'] = $userProfileCompleteChart
            ->setCommunity($community)
            ->setType(UserProfileCompleteChart::TYPE_ONBOARDING)
            ->getChart(Chart::TYPE_PIE);


        return $this->render('manager/community/analytics.html.twig', [
            'charts' => $chart,
            'community' => $community,
            'connectParams' => $userConnectChart->getParams(),
        ]);
    }

    /**
     * @Route("/manager/community/{url}/users", name="manager_community_users")
     * @ParamConverter("url", options={"mapping": {"url": "url"}})
     * @param Community $community
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function users(
        Community $community,
        PaginatorInterface $paginator,
        Request $request,
        UserRepository $userRepository
    )
    {
        $pagination = $paginator->paginate(
            $userRepository->getUserByCommunityQuery($community),
            $request->query->getInt('page', 1),
            50
        );

        return $this->render('manager/community/users.html.twig', [
            'community' => $community,
            'users' => $pagination,
        ]);
    }
}
