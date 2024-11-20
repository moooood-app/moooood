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

    /**
     * @todo Implement pagination or limit
     *
     * @return array<T>
     */
    public function getMetrics(User $user, GroupingCriteria $groupingCriteria, ?Processor $processor = null): array
    {
        $entityManager = $this->getEntityManager();

        $mapping = new ResultSetMappingBuilder($this->getEntityManager());
        $mapping->addRootEntityFromClassMetadata($this->getClassName(), 'm');

        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder = $this->getQueryBuilder($groupingCriteria);

        $parameters = [
            self::USER_PARAMETER => $user->getId(),
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
