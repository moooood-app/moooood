<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\Complexity;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractProcessorMetricsRepository<Complexity>
 */
class ComplexityRepository extends AbstractProcessorMetricsRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Complexity::class);
    }

    protected function updateQueryBuilder(QueryBuilder $builder, MetricsQuery $query): QueryBuilder
    {
        $builder
            ->addSelect("AVG((metadata->>'smog_index')::float) as smog_index")
            ->addSelect("AVG((metadata->>'complexity_rating')::float) as complexity_rating")
            ->addSelect("AVG((metadata->>'gunning_fog_index')::float) as gunning_fog_index")
            ->addSelect("AVG((metadata->>'coleman_liau_index')::float) as coleman_liau_index")
            ->addSelect("AVG((metadata->>'flesch_reading_ease')::float) as flesch_reading_ease")
            ->addSelect("AVG((metadata->>'linsear_write_formula')::float) as linsear_write_formula")
            ->addSelect("AVG((metadata->>'readability_consensus')::float) as readability_consensus")
            ->addSelect("AVG((metadata->>'flesch_kincaid_grade_level')::float) as flesch_kincaid_grade_level")
            ->addSelect("AVG((metadata->>'automated_readability_index')::float) as automated_readability_index")
            ->addSelect("AVG((metadata->>'dale_chall_readability_score')::float) as dale_chall_readability_score")
        ;

        return $builder;
    }
}
