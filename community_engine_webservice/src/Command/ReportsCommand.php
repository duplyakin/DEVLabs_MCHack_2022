<?php

namespace App\Command;

use App\Entity\Call;
use App\Entity\Community;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class ReportsCommand
 * @package App\Command
 */
class ReportsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:reports';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Community
     */
    private $community;

    /**
     * @var string
     */
    private $email;
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var array
     */
    private $csvData = [];

    /**
     * ReportsCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     * @param MailerInterface $mailer
     */
    public function __construct(
        ?string $name = null,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    )
    {
        set_time_limit(0);
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Create reports');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $emailQuestion = new Question('Type your email:', 'ad@meetsup.co');
        $this->email = $helper->ask($input, $output, $emailQuestion);

        $communities = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Community::class, 'c', 'c.id')
            ->getQuery()
            ->getResult();

        $question = new ChoiceQuestion(
            'Please select community',
            $communities,
            0
        );
        $question->setErrorMessage('Community %s is invalid.');

        $community = $helper->ask($input, $output, $question);

        /** @var Community $community */
        $this->community = $this->entityManager->getRepository(Community::class)->findOneBy([
            'title' => $community,
        ]);

        $question = new ChoiceQuestion(
            'Please select report',
            [
                'User info',
                'Rate',
                'Retention',
                'Connects',
                'All',
                'Summary',
            ],
            0
        );
        $question->setErrorMessage('Community %s is invalid.');

        $report = $helper->ask($input, $output, $question);

        switch ($report) {
            case 'User info':
                $this->userInfo();
                break;
            case 'Rate':
                $this->rate();
                break;
            case 'Connects':
                $this->connect();
                break;
            case 'Retention':
                $q = new ConfirmationQuestion('For community ' . $this->community->getTitle() . '? ', true);
                $c = $helper->ask($input, $output, $q);
                $this->retention($c);
                break;
            case 'Summary':
                $this->summary($io);
                break;
            case 'All':
                $this->userInfo();
                $this->rate();
                $this->connect();
                $this->retention(true);
                break;

        }

        $this->send();

        $io->success('success');

        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle $io
     */
    private function summary(SymfonyStyle $io)
    {
        /** @var Community[] $communities */
        $communities = $this->entityManager->getRepository(Community::class)->findAll();
        foreach ($communities as $community) {
            $io->section($community->getTitle());
            $this->community = $community;

            $this->userInfo();
            $this->rate();
            $this->connect();
            $this->retention(true);

            $io->success('ok');
        }
    }

    /**
     * @param $forCommunity
     */
    private function retention($forCommunity)
    {
        $connects = $this->entityManager->createQueryBuilder()
            ->select([
                'WEEK(c.created_at) as week_num',
                'YEAR(c.created_at) as year_num',
                'MIN(c.created_at) as created_at',
            ])
            ->from(Call::class, 'c')
            ->andWhere('c.created_at > \'2020-08-15\'')
            ->groupBy('week_num, year_num')
            ->orderBy('created_at')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (empty($connects)) {
            return;
        }

        $header = [''];
        for ($i = 0; $i < count($connects); $i++) {
            $connects[$i]['created_at'] = new \DateTime($connects[$i]['created_at']);
            $users = $this->entityManager->createQueryBuilder()
                ->select([
                    'u.id',
                    'u.created_at',
                ])
                ->andWhere('u.created_at > \'2020-08-15\'')
                ->from(User::class, 'u', 'u.id')
                ->groupBy('u.id');

            if ($i == 0) {
                $users
                    ->andWhere('WEEK(u.created_at) <= :week')
                    ->andWhere('YEAR(u.created_at) <= :year')
                    ->setParameters([
                        'week' => $connects[$i]['week_num'],
                        'year' => $connects[$i]['year_num'],
                    ]);
            } elseif (!isset($connects[$i + 1])) {
                $users
                    ->andWhere('WEEK(u.created_at) > :week')
                    ->andWhere('YEAR(u.created_at) >= :year')
                    ->setParameters([
                        'week' => $connects[$i - 1]['week_num'],
                        'year' => $connects[$i - 1]['year_num'],
                    ]);
            } else {
                $users
                    ->andWhere('
                        (WEEK(u.created_at) > :prev_week and WEEK(u.created_at) < :next_week and YEAR(u.created_at) = :prev_year)
                            or
                        (WEEK(u.created_at) > :prev_week and WEEK(u.created_at) > :next_week and YEAR(u.created_at) >= :prev_year and YEAR(u.created_at) < :next_year)
                            or
                        (WEEK(u.created_at) < :prev_week and WEEK(u.created_at) < :next_week and YEAR(u.created_at) > :prev_year and YEAR(u.created_at) <= :next_year)
                    ')
                    ->setParameters([
                        'prev_week' => $connects[$i - 1]['week_num'],
                        'next_week' => $connects[$i + 1]['week_num'],
                        'prev_year' => $connects[$i - 1]['year_num'],
                        'next_year' => $connects[$i + 1]['year_num'],
                    ]);
            }

            if ($forCommunity) {
                $users
                    ->setParameter(':community', $this->community)
                    ->andWhere(':community MEMBER OF u.communities');
            }

            $connects[$i]['users'] =
                $users
                    ->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);

            $connected = $this->entityManager->createQueryBuilder()
                ->select([
                    'u.id'
                ])
                ->from(User::class, 'u', 'u.id')
                ->join('u.callItem', 'ci')
                ->join('ci.callInstance', 'c')
                ->andWhere('WEEK(c.created_at) = :week')
                ->andWhere('Year(c.created_at) = :year')
                ->setParameters([
                    'week' => $connects[$i]['week_num'],
                    'year' => $connects[$i]['year_num'],
                ]);
            if ($forCommunity) {
                $connected
                    ->setParameter(':community', $this->community)
                    ->andWhere(':community MEMBER OF u.communities');
            }

            $connects[$i]['connected'] =
                $connected->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);

            $header[] = $connects[$i]['created_at']->format('Y-m-d');
        }


        $connects = array_values($connects);
        $csv = $this->createCsv($header);
        foreach ($connects as $row => $connect) {
            $rowData = [];

            for ($col = $row; $col < count($connects); $col++) {

                $rowData[] = $col == $row ?
                    count($connect['users']) :
                    count(array_intersect_key($connect['users'], $connects[$col]['connected']));
            }

            $rowDataCount = count($rowData);
            for ($rd = 0; $rd < (count($connects) - $rowDataCount); $rd++) {
                array_unshift($rowData, '');
            }
            array_unshift($rowData, $connect['created_at']->format('Y-m-d'));
            $csv .= $this->createCsv($rowData);
        }

        $name = $forCommunity ? 'retention_' . $this->community->getUrl() . '.csv' : 'retention.csv';
        $this->csvData[$name] = $csv;
    }

    /**
     *
     */
    private
    function averageRate()
    {
        $users = $this->entityManager->createQueryBuilder()
            ->select(
                'u.firstName',
                'u.lastName',
                'COALESCE(u.emailAlt, u.email) as email',
                'AVG(r.rate) as rate'
            )
            ->from(User::class, 'u')
            ->join('u.me_reviews', 'r')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere('r.is_successful = true')
            ->setParameter(':community', $this->community)
            ->groupBy('u.id')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (empty($users)) {
            return;
        }

        $header = array_keys($users[0]);
        array_unshift($users, $header);

        $csv = '';
        foreach ($users as $user) {
            $csv .= $this->createCsv($user);
        }

        $this->csvData['average_rate_' . $this->community->getUrl() . '.csv'] = $csv;
    }

    /**
     *
     */
    private
    function connect()
    {
        $users = $this->entityManager->createQueryBuilder()
            ->select(
                'CONCAT(u.firstName, \' \', u.lastName, \', \', COALESCE(u.emailAlt, u.email)) as user_1',
                'c.created_at',
                'CONCAT(u2.firstName, \' \', u2.lastName, \', \', COALESCE(u2.emailAlt, u2.email)) as user_2'
            )
            ->from(User::class, 'u')
            ->join('u.callItem', 'cu')
            ->join('cu.callInstance', 'c')
            ->join('c.users', 'cu2', 'WITH', 'cu2.user != u')
            ->join('cu2.user', 'u2')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere(':community MEMBER OF u2.communities')
            ->andWhere('u.id != 1')
            ->andWhere('u2.id != 1')
            ->setParameter(':community', $this->community)
            ->orderBy('c.created_at')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (empty($users)) {
            return;
        }

        $header = array_keys($users[0]);
        array_unshift($users, $header);

        $csv = '';
        foreach ($users as $user) {
            if (isset($user['created_at'])) {
                $user['created_at'] = $user['created_at']->format('Y-m-d');
            }
            $csv .= $this->createCsv($user);
        }

        $this->csvData['connects_' . $this->community->getUrl() . '.csv'] = $csv;
    }

    /**
     *
     */
    private
    function rate()
    {
        $ratings = $this->entityManager->createQueryBuilder()
            ->select(
                'u.firstName',
                'u.lastName',
                'COALESCE(u.emailAlt, u.email) as email',
                'r.is_successful',
                'r.rate',
                'rtu.firstName as firstNameTo',
                'rtu.lastName as lastNameTo',
                'COALESCE(rtu.emailAlt, rtu.email) as emailTo'
            )
            ->from(User::class, 'u')
            ->join('u.reviews', 'r')
            ->join('r.rate_to', 'rtu')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere(':community MEMBER OF rtu.communities')
            ->andWhere('u.id != 1')
            ->andWhere('rtu.id != 1')
            ->setParameter(':community', $this->community)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (empty($ratings)) {
            return;
        }

        $csv = '';
        $header = array_keys($ratings[0]);
        array_unshift($ratings, $header);
        foreach ($ratings as $rate) {
            if (isset($rate['is_successful'])) {
                $rate['is_successful'] = $rate['is_successful'] ? 'Y' : 'N';
                $rate['rate'] = $rate['rate'] ? $rate['rate'] : 'N';
            }
            $csv .= $this->createCsv($rate);
        }

        $this->csvData['rate_' . $this->community->getUrl() . '.csv'] = $csv;
    }

    /**
     *
     */
    private
    function userInfo()
    {
//        $connects = $this->entityManager->createQueryBuilder()
//            ->select('COUNT(c.id)')
//            ->from(Call::class, 'c')
//            ->join('c.users', 'cu')
//            ->join('c.users', 'cun', 'cun.user_id != cu.user_id')
//            ->join('cun.user', 'un')
//            ->andWhere(':community MEMBER OF un.communities')
//            ->andWhere('cu.user = u')
//            ->groupBy('cu.user');

        $users = $this->entityManager->createQueryBuilder()
            ->select([
                'COALESCE(u.emailAlt, u.email) as email',
                'u.telegramUsername as telegram',
                'u.firstName as first_name',
                'u.lastName as last_name',
                'u.facebookLink as facebook',
                'u.linkedinLink as linkedin',
                'u.created_at',
                'u.profile_complete',
                'u.about',
                'u.looking_for',
                'GROUP_CONCAT(DISTINCT a.title SEPARATOR \', \') as answers',
//                'u.id',
                //'(' . $connects->getDQL() . ') as connects',
            ])
            ->from(User::class, 'u')
            ->leftJoin('u.answers', 'a')
//            ->join('u.callItem', 'ci')
//            ->join('ci.callInstance', 'call')
            ->andWhere(':community MEMBER OF u.communities')
            ->andWhere('u.id != 1')
            ->setParameter(':community', $this->community)
            ->groupBy('u.id')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (empty($users)) {
            return;
        }

        $header = array_keys($users[0]);
        array_unshift($users, $header);
        $csv = '';

        foreach ($users as $user) {
            if (isset($user['created_at'])) {
                $user['created_at'] = $user['created_at']->format('Y-m-d');
            }
            $csv .= $this->createCsv($user);
        }
        $this->csvData['users_' . $this->community->getUrl() . '.csv'] = $csv;
    }

    /**
     * @param $data
     * @return bool|string
     */
    private
    function createCsv($data)
    {
        $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');
        fputcsv($csv, $data);
        rewind($csv);

        return stream_get_contents($csv);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    private
    function send()
    {
        $email = (new Email())
            ->from('support@meetsup.io')
            ->to($this->email)
            ->subject('Report')
            ->text($this->community->getTitle() . ' Report');

        foreach ($this->csvData as $name => $csv) {
            if (empty($csv)) {
                continue;
            }
            $email->attach($csv, $name, 'text/csv');
        }

        $this->mailer->send($email);
    }
}
