<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;


use App\Entity\Community;
use App\Entity\MetricOrder;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserMetric;
use App\Entity\UserMetricField;
use App\Message\ConnectMessage;
use App\Repository\CommunityRepository;
use App\Repository\MetricOrderRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserMetricFieldRepository;
use App\Repository\UserRepository;
use App\Service\Balance\BalanceHandlerService;
use App\Service\Metric\SortService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MetricController
 * @package App\Controller\Admin
 */
class MetricController extends AbstractCrudController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SortService
     */
    private $sortService;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var CrudUrlGenerator
     */
    private $crudUrlGenerator;
    /**
     * @var BalanceHandlerService
     */
    private $balanceHandlerService;
    /**
     * @var Request
     */
    private $request;

    /**
     * MetricController constructor.
     * @param EntityManagerInterface $entityManager
     * @param SortService $sortService
     * @param MessageBusInterface $bus
     * @param CrudUrlGenerator $crudUrlGenerator
     * @param BalanceHandlerService $balanceHandlerService
     * @param RequestStack $request
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SortService $sortService,
        MessageBusInterface $bus,
        CrudUrlGenerator $crudUrlGenerator,
        BalanceHandlerService $balanceHandlerService,
        RequestStack $request
    )
    {
        $this->entityManager = $entityManager;
        $this->sortService = $sortService;
        $this->bus = $bus;
        $this->crudUrlGenerator = $crudUrlGenerator;
        $this->balanceHandlerService = $balanceHandlerService;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/adm-z23db/metric", name="admin_metric")
     * @param AdminContext $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(AdminContext $context)
    {
        /** @var CommunityRepository $communityRepository */
        $communityRepository = $this->entityManager->getRepository(Community::class);
        $communities = $communityRepository->findAll();

        $users = [];
        $community = null;
        $orders = [];

        if ($this->request->get('communityId') && $community = $communityRepository->find($this->request->get('communityId'))) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->entityManager->getRepository(User::class);
            $users = $userRepository->findAllForMetricQueryBuilder($community);

            $connectedUsers = $userRepository->findAllConnected();

            $users = $this->sortService
                ->setConnectedUsers($connectedUsers)
                ->getSortedUsers($users);

            /** @var MetricOrderRepository $metricOrderRepository */
            $metricOrderRepository = $this->entityManager->getRepository(MetricOrder::class);
            $orders = $metricOrderRepository->findOrderedUsers($community);
        }

        /** @var UserMetricFieldRepository $fieldRepository */
        $fieldRepository = $this->entityManager->getRepository(UserMetricField::class);
        $fields = $fieldRepository->findAll();

        /** @var QuestionRepository $questionRepository */
        $questionRepository = $this->entityManager->getRepository(Question::class);
        $questions = $questionRepository->findAll();

        return $this->render('admin/metric/index.html.twig', [
            'fields' => $fields,
            'users' => $users,
            'questions' => $questions,
            'unsortedUsers' => $this->sortService->getUnsortedUsers(),
            'orders' => $orders,
            'community' => $community,
            'communities' => $communities,
        ]);
    }

    /**
     * @Route("/adm-z23db/metric/connect", name="admin_metric_connect")
     */
    public function connect()
    {
        $connect = new ConnectMessage();
        $this->bus->dispatch($connect);
        $this->addFlash('success', 'Success start Margo');
        $url = $this->crudUrlGenerator->build()
            ->setController(MetricController::class)
            ->setAction('index')
            ->generateUrl();
        return $this->redirect($url);
    }

    /**
     * @Route("/adm-z23db/metric/save-balance/{user}/{community}/{value}", name="admin_metric_balance_save")
     * @param User $user
     * @param Community $community
     * @param $value
     * @return JsonResponse
     * @throws \App\Exception\Balance\BalanceHandlerException
     * @throws \Throwable
     */
    public function saveBalanceValue(User $user, Community $community, $value)
    {
        if ($value > 0) {
            $this->balanceHandlerService->handle(new \App\Service\Payment\Deposit\Manual($user, (int)$value, $community));
            return new JsonResponse([
                'status' => 'success',
            ]);
        }

        if ($value < 0) {
            $this->balanceHandlerService->handle(new \App\Service\Payment\Spend\Manual($user, (int)$value, $community));
            return new JsonResponse([
                'status' => 'success',
            ]);
        }

        return new JsonResponse([
            'status' => 'value is null or 0',
        ]);
    }

    /**
     * @Route("/adm-z23db/metric/save-order/{user}/{withUser}/{community}", name="admin_metric_order_save")
     * @param User $user
     * @param User $withUser
     * @param Community $community
     * @param MetricOrderRepository $repository
     * @return JsonResponse
     */
    public function saveOrder(User $user, User $withUser, Community $community, MetricOrderRepository $repository)
    {
        $metric = $repository->findOneBy([
            'user' => $user,
            'withUser' => $withUser,
            'community' => $community,
        ]);
        if (!($metric instanceof MetricOrder)) {
            $metric = new MetricOrder();
            $metric->setUser($user);
            $metric->setWithUser($withUser);
            $metric->setCommunity($community);
        }
//        if ($withUser == 'null' || empty($withUser) || $withUser == 0) {
//            $this->entityManager->remove($metric);
//            $this->entityManager->flush();
//            return new JsonResponse([
//                'status' => 'success entity removed',
//            ]);
//        }
        $metric->setWithUser($withUser);
        $this->entityManager->persist($metric);
        $this->entityManager->flush();
        return new JsonResponse([
            'status' => 'success',
        ]);
    }

    /**
     * @Route("/adm-z23db/metric/delete-order", name="admin_metric_order_delete")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteOrder(Request $request)
    {
        /** @var MetricOrderRepository $repository */
        $repository = $this->entityManager->getRepository(MetricOrder::class);
        $metricOrder = $repository->find($request->get('metricOrder'));
        if ($metricOrder) {
            $this->entityManager->remove($metricOrder);
            $this->entityManager->flush();
            $this->addFlash('success', 'Order removed');
        } else {
            $this->addFlash('error', 'Can\'t remove order');
        }
        $url = $this->get(AdminUrlGenerator::class)
            ->setRoute('admin_metric')
            ->set('communityId', (int)$request->get('community'))
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @Route("/adm-z23db/metric/save/{user}/{value}/{field}", name="admin_metric_save")
     * @param User $user
     * @param $value
     * @param UserMetricField $field
     * @return JsonResponse
     */
    public function saveValue(User $user, $value, UserMetricField $field)
    {
        $metric = $user->getFirstMetricByField($field);
        if (!($metric instanceof UserMetric)) {
            if ($value == 'null') {
                return new JsonResponse([
                    'status' => 'entity not exist',
                ]);
            }
            $metric = new UserMetric();
            $metric->setUser($user);
            $metric->setField($field);
        }
        if ($value == 'null' || empty($value) || $value == 0) {
            $this->entityManager->remove($metric);
            $this->entityManager->flush();
            return new JsonResponse([
                'status' => 'success entity removed',
            ]);
        }
        $metric->setValue($value);
        $this->entityManager->persist($metric);
        $this->entityManager->flush();
        return new JsonResponse([
            'status' => 'success',
        ]);
    }

    /**
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}