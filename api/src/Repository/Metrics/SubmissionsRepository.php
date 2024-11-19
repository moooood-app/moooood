<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\Metrics\Submissions;
use App\Enum\Metrics\GroupingCriteria;
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

    protected function getQueryBuilder(GroupingCriteria $groupingCriteria): QueryBuilder
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $grouping = $groupingCriteria->getSelectExpression(self::ENTRY_ALIAS);
        $builder
            ->select([
                "{$grouping} as id",
                "'{$groupingCriteria->value}' as grouping",
                'COUNT(*) as submissions',
                "SUM(LENGTH(REGEXP_REPLACE(content, '\s+', '', 'g'))) AS character_count",
                "SUM(array_length(regexp_split_to_array(content, '\s+'), 1)) AS word_count",
                "SUM(array_length(regexp_split_to_array(content, '[.!?]'), 1)) AS sentence_count",
            ])
            ->from('entries', self::ENTRY_ALIAS)
            ->where(\sprintf('%s.user_id = :user', self::ENTRY_ALIAS))
            ->groupBy($grouping)
            ->orderBy($grouping, 'ASC')
        ;

        return $builder;
    }
}
