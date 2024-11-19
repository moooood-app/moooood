<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @template T of object
 *
 * @template-extends AbstractMetricsRepository<T>
 */
abstract class AbstractProcessorMetricsRepository extends AbstractMetricsRepository
{
    protected function getQueryBuilder(GroupingCriteria $groupingCriteria): QueryBuilder
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $grouping = $groupingCriteria->getSelectExpression(self::ENTRY_ALIAS);
        $builder
            ->select([
                "{$grouping} as id",
                "'{$groupingCriteria->value}' as grouping",
            ])
            ->from('entries_metadata', 'em')
            ->leftJoin('em', 'entries', self::ENTRY_ALIAS, \sprintf('em.entry_id = %s.id', self::ENTRY_ALIAS))
            ->where(\sprintf('%s.user_id = :%s', self::ENTRY_ALIAS, self::USER_PARAMETER))
            ->andWhere(\sprintf('processor = :%s', self::PROCESSOR_PARAMETER))
            ->groupBy($grouping)
            ->orderBy($grouping, 'ASC')
        ;

        return $this->addSelects($builder);
    }

    abstract protected function addSelects(QueryBuilder $builder): QueryBuilder;
}
