<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\Metrics\Keywords;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractProcessorMetricsRepository<Keywords>
 */
class KeywordsRepository extends AbstractProcessorMetricsRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Keywords::class);
    }

    protected function addSelects(QueryBuilder $builder): QueryBuilder
    {
        $wrapper = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder
            ->addSelect('keyword_data.keyword AS keyword')
            ->addSelect('COUNT(keyword_data.keyword) AS keyword_count')
            ->addSelect('AVG(keyword_data.score) AS average_score')
            ->from('jsonb_array_elements(metadata)', 'element')
            ->from('jsonb_to_record(element)', 'keyword_data(score FLOAT, keyword TEXT)')
            ->addGroupBy('keyword_data.keyword')
        ;

        $this->addDateFilters($builder);

        $wrapper
            ->addSelect('aggregated_keywords.id')
            ->addSelect('aggregated_keywords.grouping')
            ->addSelect(<<<'SQL'
                    jsonb_object_agg(
                        keyword,
                        jsonb_build_object(
                            'count', keyword_count,
                            'average_score', average_score
                        )
                    ) AS keywords
                SQL)
            ->from('('.$builder->getSQL().') AS aggregated_keywords')
            ->groupBy('aggregated_keywords.id', 'aggregated_keywords.grouping')
        ;

        return $wrapper;
    }

    protected function shouldAddDateFilters(): bool
    {
        return false;
    }
}
