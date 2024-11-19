<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\Metrics\Sentiment;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractProcessorMetricsRepository<Sentiment>
 */
class SentimentRepository extends AbstractProcessorMetricsRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Sentiment::class);
    }

    public function addSelects(QueryBuilder $builder): QueryBuilder
    {
        $builder
            ->addSelect("AVG((metadata->>'compound')::float) as compound")
            ->addSelect("AVG((metadata->>'negative')::float) as negative")
            ->addSelect("AVG((metadata->>'positive')::float) as positive")
            ->addSelect("AVG((metadata->>'neutral')::float) as neutral")
        ;

        return $builder;
    }
}
