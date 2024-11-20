<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

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
     * @todo Implement pagination or limit
     *
     * @return array<T>
     */
    public function getMetrics(
        User $user,
        GroupingCriteria $groupingCriteria,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateUntil,
        ?Processor $processor = null,
    ): array {
        $entityManager = $this->getEntityManager();

        $mapping = new ResultSetMappingBuilder($this->getEntityManager());
        $mapping->addRootEntityFromClassMetadata($this->getClassName(), 'm');

        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder = $this->getQueryBuilder($groupingCriteria);

        $builder
            ->andWhere(\sprintf('%s.created_at >= :%s', self::ENTRY_ALIAS, self::FROM_PARAMETER))
            ->andWhere(\sprintf('%s.created_at <= :%s', self::ENTRY_ALIAS, self::UNTIL_PARAMETER))
        ;

        $parameters = [
            self::USER_PARAMETER => $user->getId(),
            self::FROM_PARAMETER => $dateFrom->format('Y-m-d H:i:s'),
            self::UNTIL_PARAMETER => $dateUntil->format('Y-m-d H:i:s'),
        ];

        if (null !== $processor) {
            $parameters[self::PROCESSOR_PARAMETER] = $processor->value;
        }

        return $entityManager // @phpstan-ignore-line
            ->createNativeQuery($builder->getSQL(), $mapping)
            ->setParameters($parameters)
            ->getArrayResult()
        ;
    }

    abstract protected function getQueryBuilder(GroupingCriteria $groupingCriteria): QueryBuilder;
}
