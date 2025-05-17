<?php

namespace App\Awards\Checkers;

use App\Awards\AwardAwareTrait;
use App\Awards\AwardStatus;
use App\Awards\ChainableCheckerTrait;
use App\Awards\Contracts\ChainableAwardCheckerInterface;
use App\Entity\Metrics\Sentiment;
use App\Entity\User;
use App\Enum\AwardType;
use App\Enum\Metrics\MetricsGrouping;
use App\Enum\Processor;
use App\Repository\Metrics\MetricsQuery;
use App\Repository\Metrics\SentimentRepository;

final class PositivyImprovementChecker implements ChainableAwardCheckerInterface
{
    use AwardAwareTrait;
    use ChainableCheckerTrait;

    public const IMPROVEMENT_PERCENTAGE = 'improvement_percentage';

    public function __construct(private readonly SentimentRepository $sentimentRepository)
    {
    }

    public static function getSupportedType(): AwardType
    {
        return AwardType::POSITIVITY_WEEKLY;
    }

    public function check(User $user): AwardStatus
    {
        /** @var array<Sentiment> */
        $metrics = $this->sentimentRepository->getMetrics($user, new MetricsQuery(
            MetricsGrouping::WEEK,
            new \DateTimeImmutable('2 weeks ago'),
            Processor::SENTIMENT,
        ));

        [$previousWeek, $lastWeek] = $metrics;

        /** @var array{improvement_percentage: int} */
        $criteria = $this->award->getCriteria();

        return new AwardStatus(
            $this->award,
            ($lastWeek->positive - $previousWeek->positive) * 100 >= $criteria[self::IMPROVEMENT_PERCENTAGE],
            0,
        );
    }
}
