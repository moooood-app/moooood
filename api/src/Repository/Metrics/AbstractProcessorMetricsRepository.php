<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @template T of object
 *
 * @template-extends AbstractMetricsRepository<T>
 *
 * This abstract repository should be extended when the metrics are related to a processor.
 * It provides a pre-configured QueryBuilder with the necessary joins and grouping.
 */
abstract class AbstractProcessorMetricsRepository extends AbstractMetricsRepository
{
    protected function getQueryBuilder(MetricsQuery $query): QueryBuilder
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $dateSelector = $query->groupingCriteria->getDateSelector(self::ENTRY_ALIAS);
        $builder
            ->select("{$dateSelector} as date")
            ->from('entries_metadata', 'em')
            ->leftJoin('em', 'entries', self::ENTRY_ALIAS, \sprintf('em.entry_id = %s.id', self::ENTRY_ALIAS))
            ->where(\sprintf('%s.user_id = :%s', self::ENTRY_ALIAS, self::USER_PARAMETER))
            ->andWhere(\sprintf('processor = :%s', self::PROCESSOR_PARAMETER))
            ->groupBy($dateSelector)
            ->orderBy($dateSelector, 'ASC')
        ;

        return $builder;
    }
}
