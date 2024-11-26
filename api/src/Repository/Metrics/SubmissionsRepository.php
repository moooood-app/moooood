<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\Submissions;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractMetricsRepository<Submissions>
 */
class SubmissionsRepository extends AbstractMetricsRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Submissions::class);
    }

    protected function getQueryBuilder(MetricsQuery $query): QueryBuilder
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $dateSelector = $query->groupingCriteria->getDateSelector(self::ENTRY_ALIAS);
        $builder
            ->addSelect("{$dateSelector} as date")
            ->addSelect('COUNT(*) as submissions')
            ->addSelect("SUM(LENGTH(REGEXP_REPLACE(content, '\\s+', '', 'g'))) AS character_count")
            ->addSelect("SUM(array_length(regexp_split_to_array(content, '\\s+'), 1)) AS word_count")
            ->addSelect("SUM(array_length(regexp_split_to_array(content, '[.!?]'), 1)) AS sentence_count")
            ->from('entries', self::ENTRY_ALIAS)
            ->where(\sprintf('%s.user_id = :user', self::ENTRY_ALIAS))
            ->groupBy($dateSelector)
            ->orderBy($dateSelector, 'ASC')
        ;

        return $builder;
    }

    protected function addSelects(QueryBuilder $builder, MetricsQuery $query): QueryBuilder
    {
        return $builder;
    }
}
