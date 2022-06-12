<?php

namespace App\MessageHandler;

use App\Entity\UserMetric;
use App\Message\MLSentimentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class MLSentimentMessageHandler
 * @package App\MessageHandler
 */
final class MLSentimentMessageHandler implements MessageHandlerInterface
{
    /**
     *
     */
    const BONUS_FIELD_ID = 5;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * MLSentimentMessageHandler constructor.
     * @param HttpClientInterface $httpClient
     * @param ParameterBagInterface $parameterBag
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        EntityManagerInterface $entityManager
    )
    {
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
    }

    /**
     * @param MLSentimentMessage $message
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function __invoke(MLSentimentMessage $message)
    {
        $response = $this->httpClient->request('POST', $this->parameterBag->get('sentiment_dp_uri'), [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => json_encode([
                'x' => $message->getText(),
            ]),
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body = json_decode($response->getContent(), true);

        if (!isset($body[0][0])) {
            return;
        }

        switch ($body[0][0]) {
            case 'negative':
                $this->userBonus($message->getUserId(), -1);
                break;
            case 'positive':
                $this->userBonus($message->getUserId(), 1);
                break;
        }
    }

    /**
     * @param int $userId
     * @param $value
     */
    protected function userBonus(int $userId, $value)
    {
        /** @var UserMetric $metric */
        $metric = $this->entityManager
            ->getRepository(UserMetric::class)
            ->findOneBy([
                'user' => $userId,
                'field' => self::BONUS_FIELD_ID,
            ]);

        if ($metric) {
            $metric->setValue($metric->getValue() + $value);
            $this->entityManager->persist($metric);
            $this->entityManager->flush();
        }

    }
}
