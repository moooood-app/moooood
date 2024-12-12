<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\Metrics\Emotions;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractProcessorMetricsRepository<Emotions>
 */
class EmotionsRepository extends AbstractProcessorMetricsRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Emotions::class);
    }

    protected function updateQueryBuilder(QueryBuilder $builder, MetricsQuery $query): QueryBuilder
    {
        $builder
            ->addSelect("AVG((metadata->>'joy')::float) as joy")
            ->addSelect("AVG((metadata->>'fear')::float) as fear")
            ->addSelect("AVG((metadata->>'love')::float) as love")
            ->addSelect("AVG((metadata->>'anger')::float) as anger")
            ->addSelect("AVG((metadata->>'grief')::float) as grief")
            ->addSelect("AVG((metadata->>'pride')::float) as pride")
            ->addSelect("AVG((metadata->>'caring')::float) as caring")
            ->addSelect("AVG((metadata->>'desire')::float) as desire")
            ->addSelect("AVG((metadata->>'relief')::float) as relief")
            ->addSelect("AVG((metadata->>'disgust')::float) as disgust")
            ->addSelect("AVG((metadata->>'neutral')::float) as neutral")
            ->addSelect("AVG((metadata->>'remorse')::float) as remorse")
            ->addSelect("AVG((metadata->>'sadness')::float) as sadness")
            ->addSelect("AVG((metadata->>'approval')::float) as approval")
            ->addSelect("AVG((metadata->>'optimism')::float) as optimism")
            ->addSelect("AVG((metadata->>'surprise')::float) as surprise")
            ->addSelect("AVG((metadata->>'amusement')::float) as amusement")
            ->addSelect("AVG((metadata->>'annoyance')::float) as annoyance")
            ->addSelect("AVG((metadata->>'confusion')::float) as confusion")
            ->addSelect("AVG((metadata->>'curiosity')::float) as curiosity")
            ->addSelect("AVG((metadata->>'gratitude')::float) as gratitude")
            ->addSelect("AVG((metadata->>'admiration')::float) as admiration")
            ->addSelect("AVG((metadata->>'excitement')::float) as excitement")
            ->addSelect("AVG((metadata->>'disapproval')::float) as disapproval")
            ->addSelect("AVG((metadata->>'nervousness')::float) as nervousness")
            ->addSelect("AVG((metadata->>'realization')::float) as realization")
            ->addSelect("AVG((metadata->>'embarrassment')::float) as embarrassment")
            ->addSelect("AVG((metadata->>'disappointment')::float) as disappointment")
        ;

        return $builder;
    }
}
