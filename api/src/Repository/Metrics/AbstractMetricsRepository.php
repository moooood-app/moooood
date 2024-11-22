<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;
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
    protected const ENTRY_ALIAS = 'e';
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
        $mapping->addRootEntityFromClassMetadata($this->getClassName(), 'm');

        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder = $this->getQueryBuilder($query->groupingCriteria);

        if ($this->shouldAddDateFilters()) {
            $this->addDateFilters($builder);
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
            ->createNativeQuery($builder->getSQL(), $mapping)
            ->setParameters($parameters)
            ->getArrayResult()
        ;
    }

    abstract protected function getQueryBuilder(GroupingCriteria $groupingCriteria): QueryBuilder;

    abstract protected function shouldAddDateFilters(): bool;

    protected function addDateFilters(QueryBuilder $builder): void
    {
        $builder
            ->andWhere(\sprintf('%s.created_at >= :%s', self::ENTRY_ALIAS, self::FROM_PARAMETER))
            ->andWhere(\sprintf('%s.created_at <= :%s', self::ENTRY_ALIAS, self::UNTIL_PARAMETER))
        ;
    }
}
