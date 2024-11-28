<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Part;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @template T of object
 *
 * @template-extends ServiceEntityRepository<T>
 *
 * You should extend this abstract repository when the metrics are not directly related to a processor.
 */
abstract class AbstractMetricsRepository extends ServiceEntityRepository implements MetricsRepositoryInterface
{
    protected const ROOT_ALIAS = 'm';
    protected const ENTRY_ALIAS = 'e';
    protected const PART_ALIAS = 'p';
    protected const USER_PARAMETER = 'user';
    protected const PROCESSOR_PARAMETER = 'processor';
    protected const FROM_PARAMETER = 'from';
    protected const UNTIL_PARAMETER = 'until';

    /**
     * @return array<T>
     */
    public function getMetrics(
        User $user,
        MetricsQuery $query,
    ): array {
        $entityManager = $this->getEntityManager();

        $mapping = new ResultSetMappingBuilder($this->getEntityManager());
        $mapping->addRootEntityFromClassMetadata($this->getClassName(), self::ROOT_ALIAS);

        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder = $this->getQueryBuilder($query);

        $doctrineIdentifierForPart = "'none'";

        if ($query->groupByParts) {
            $builder
                ->addSelect(\sprintf('%s.id as part_id', self::PART_ALIAS))
                ->addSelect(\sprintf('%s.name as part_name', self::PART_ALIAS))
                ->addSelect(\sprintf('%s.colors as part_colors', self::PART_ALIAS))
                ->leftJoin(self::ENTRY_ALIAS, 'parts', self::PART_ALIAS, \sprintf('%s.part_id = %s.id', self::ENTRY_ALIAS, self::PART_ALIAS))
                ->addGroupBy(\sprintf('%s.id', self::PART_ALIAS))
            ;

            $mapping->addJoinedEntityFromClassMetadata(
                Part::class,
                self::PART_ALIAS,
                self::ROOT_ALIAS,
                'part',
                [
                    'id' => 'part_id',
                    'name' => 'part_name',
                    'colors' => 'part_colors',
                ],
            );

            $doctrineIdentifierForPart = \sprintf("COALESCE(%s.id::text, 'none')", self::PART_ALIAS);
        }

        $builder->addSelect(\sprintf(
            "CONCAT(%s, '-', %s, '-%s-', %s) as id",
            $query->groupingCriteria->getDateSelector(self::ENTRY_ALIAS),
            $query->groupingCriteria->getGroupByExpression(self::ENTRY_ALIAS),
            $query->groupingCriteria->value,
            $doctrineIdentifierForPart,
        ));

        $qb = $this->updateQueryBuilder($builder, $query);

        // We add the date filters only if a new builder was not returned
        // Otherwise, it's up to the implementation to add the filters
        // todo: that is a bit confusing and could be refactored
        if ($qb === $builder) {
            $this->addDateFilters($qb);
        }

        $parameters = [
            self::USER_PARAMETER => $user->getId(),
            self::FROM_PARAMETER => $query->getDateFrom()->format('Y-m-d H:i:s'),
            self::UNTIL_PARAMETER => $query->getDateUntil()->format('Y-m-d H:i:s'),
        ];

        if (null !== $query->processor) {
            $parameters[self::PROCESSOR_PARAMETER] = $query->processor->value;
        }

        return $entityManager // @phpstan-ignore-line
            ->createNativeQuery($qb->getSQL(), $mapping)
            ->setParameters($parameters)
            ->getResult()
        ;
    }

    abstract protected function getQueryBuilder(MetricsQuery $query): QueryBuilder;

    abstract protected function updateQueryBuilder(QueryBuilder $builder, MetricsQuery $query): QueryBuilder;

    protected function addDateFilters(QueryBuilder $builder): void
    {
        $builder
            ->andWhere(\sprintf('%s.created_at >= :%s', self::ENTRY_ALIAS, self::FROM_PARAMETER))
            ->andWhere(\sprintf('%s.created_at < :%s', self::ENTRY_ALIAS, self::UNTIL_PARAMETER))
        ;
    }
}
