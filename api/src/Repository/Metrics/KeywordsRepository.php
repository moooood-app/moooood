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

    protected function updateQueryBuilder(QueryBuilder $builder, MetricsQuery $query): QueryBuilder
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
        ;

        $extraProperties = ['id', 'date'];

        foreach ($extraProperties as $column) {
            $wrapper->addSelect("aggregated_keywords.{$column}");
            $wrapper->addGroupBy("aggregated_keywords.{$column}");
        }

        return $wrapper;
    }
}
