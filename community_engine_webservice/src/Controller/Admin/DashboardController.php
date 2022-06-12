<?php

namespace App\Controller\Admin;

use App\Entity\Answer;
use App\Entity\BalanceTransaction;
use App\Entity\Call;
use App\Entity\CallStep;
use App\Entity\Certificate;
use App\Entity\Community;
use App\Entity\Notification\NotificationTransport;
use App\Entity\PriorityMetricKeyword;
use App\Entity\Question;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserAdmin;
use App\Entity\UserMetricField;
use App\Repository\CommunityRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use App\Service\Call\MakeCallService;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller\Admin
 */
class DashboardController extends AbstractDashboardController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var MakeCallService
     */
    protected $makeCallService;
    /**
     * @var Request
     */
    private $request;

    /**
     * DashboardController constructor.
     * @param EntityManagerInterface $entityManager
     * @param MakeCallService $makeCallService
     * @param Request $request
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MakeCallService $makeCallService,
        RequestStack $request
    )
    {
        $this->entityManager = $entityManager;
        $this->makeCallService = $makeCallService;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/adm-z23db", name="admin")
     * @return Response
     */
    public function index(): Response
    {
        /** @var CommunityRepository $community */
        $repository = $this->entityManager->getRepository(Community::class);
        $community = $repository->find($this->request->get('communityId', false));
        $communities = $repository->findAll();

        $users = [];
        $holdedUsers = [];
        $questions = [];

        if ($community) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->entityManager->getRepository(User::class);
            /** @var QuestionRepository $questionRepository */
            $questionRepository = $this->entityManager->getRepository(Question::class);
            $questions = $questionRepository->findAll();
            $users = $userRepository->findAllWithQuestionByCommunity($community);
            $holdedUsers = $userRepository->findBy([
                'hold' => true,
            ]);
        }
        return $this->render('admin/dashboard/index.html.twig', [
            'users' => $users,
            'holdedUsers' => $holdedUsers,
            'questions' => $questions,
            'communities' => $communities,
        ]);
    }

    /**
     * @Route("/adm-z23db/match/{community}", name="admin.match")
     * @param Request $request
     * @param Community $community
     * @return JsonResponse
     * @throws \Throwable
     */
    public function match(Request $request, Community $community)
    {
        if (!$community) {
            return new JsonResponse([
                'error' => 'wrong params',
            ]);
        }
        $ids = json_decode($request->getContent());
        $ids = array_unique($ids);
        if (count($ids) != 2) {
            return new JsonResponse([
                'error' => 'wrong params',
            ]);
        }
        $status = (bool)$this->makeCallService->createFromIds($ids, $community) ? 'success' : 'error';
        return new JsonResponse([
            'status' => $status,
        ]);
    }

    /**
     * @Route("/adm-z23db/filter-user/{user}/{answer}", name="admin.filter-user")
     * @param User $user
     * @param Answer $answer
     * @return Response
     */
    public function filterUser(User $user, Answer $answer)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAllFilteredUsers($user, $answer);
        /** @var QuestionRepository $questionRepository */
        $questionRepository = $this->entityManager->getRepository(Question::class);
        $questions = $questionRepository->findAll();
        $content = '';
        foreach ($users as $user) {
            $content .= $this->renderView('admin/dashboard/_user.html.twig', [
                'user' => $user,
                'questions' => $questions,
            ]);
        }
        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/adm-z23db/hold-user/{user}/{type}", name="admin.hold-user")
     * @param User $user
     * @param string $type
     * @return JsonResponse
     */
    public function holdUser(User $user, string $type)
    {
        $type = $type == 'hold' ? true : false;
        $user->setHold($type);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->json([]);
    }

    /**
     * @Route("/adm-z23db/in-call/{userId}", name="admin.in-call")
     * @param int $userId
     * @return JsonResponse
     */
    public function inCall(int $userId)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'id');
        $rsm->addIndexByScalar('user_id');

        //TODO SUKA GORIT OCHAG
        $e = $this->entityManager->getConnection()->getDriver() instanceof Driver ? '"' : '`';

        $query = $this->entityManager->createNativeQuery('
            select cu2.user_id from call_user cu
            left join ' . $e . 'call' . $e . ' c on c.id = cu.call_instance_id
            left join call_user cu2 on c.id = cu2.call_instance_id
            where cu.user_id = :userId and cu2.user_id != :userId
        ', $rsm);
        $query->setParameter('userId', $userId);
        return $this->json([
            'ids' => array_keys($query->getResult())
        ]);
    }

    /**
     * @return Dashboard
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fa fa-play-circle"></i> MEETS!UP');
    }

    /**
     * @return iterable
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Metrics');
        yield MenuItem::linktoRoute('Margo', 'fas fa-balance-scale', 'admin_metric');
        yield MenuItem::linkToCrud('Fields', 'fas fa-sliders-h', UserMetricField::class);
        yield MenuItem::linkToCrud('Keywords', 'fas fa-key', PriorityMetricKeyword::class);
//        yield MenuItem::linkToCrud('Multipliers', 'fas fa-asterisk', UserMetricMultiplier::class);

        yield MenuItem::section('Notifications');
        yield MenuItem::linkToCrud('Transport', 'fa fa-car-side', NotificationTransport::class);

        yield MenuItem::section('Quest content');
        yield MenuItem::linkToCrud('Questions', 'fa fa-question', Question::class);
        yield MenuItem::linkToCrud('Answers', 'fa fa-check', Answer::class);

        yield MenuItem::section('Connects');
        yield MenuItem::linkToCrud('Calls', 'fa fa-phone', Call::class);
        yield MenuItem::linkToCrud('Call Steps', 'fa fa-list', CallStep::class);
        yield MenuItem::linkToCrud('Reviews', 'fa fa-smile', Review::class);

        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Certificates', 'fas fa-money-bill-wave', Certificate::class);
        yield MenuItem::linkToCrud('Transactions', 'fas fa-coins', BalanceTransaction::class);
        yield MenuItem::linkToCrud('Communities', 'fa fa-user-friends', Community::class);
        yield MenuItem::linkToCrud('Admin users', 'fa fa-user-cog', UserAdmin::class);
    }
}
