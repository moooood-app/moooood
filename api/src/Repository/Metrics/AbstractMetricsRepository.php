<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template T of object
 *
 * @template-extends ServiceEntityRepository<T>
 *
 * @implements MetricsRepositoryInterface<T>
 */
abstract class AbstractMetricsRepository extends ServiceEntityRepository implements MetricsRepositoryInterface
{
    protected const ENTRY_ALIAS = 'e';

    private CacheInterface $cache;

    #[Required]
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @todo Implement pagination or limit
     *
     * @return array<T>
     */
    public function getMetrics(User $user, GroupingCriteria $groupingCriteria): array
    {
        $processor = $this->getProcessor();
        $cacheKey = \sprintf(
            '%s_metrics_%s_%s',
            $processor->value,
            $user->getId(),
            $groupingCriteria->value
        );

        return $this->cache->get( // @phpstan-ignore-line
            $cacheKey,
            /** @return array<Complexity> */
            function () use ($user, $groupingCriteria, $processor): array {
                $entityManager = $this->getEntityManager();

                $mapping = new ResultSetMappingBuilder($this->getEntityManager());
                $mapping->addRootEntityFromClassMetadata($this->getClassName(), 'm');

                $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

                $grouping = $groupingCriteria->getSelectExpression(self::ENTRY_ALIAS);
                $builder
                    ->select([
                        "{$grouping} as id",
                        "'{$groupingCriteria->value}' as grouping",
                    ])
                    ->from('entries_metadata', 'em')
                    ->leftJoin('em', 'entries', self::ENTRY_ALIAS, \sprintf('em.entry_id = %s.id', self::ENTRY_ALIAS))
                    ->where(\sprintf('%s.user_id = :user', self::ENTRY_ALIAS))
                    ->andWhere('processor = :processor')
                    ->groupBy($grouping)
                    ->orderBy($grouping, 'ASC')
                ;

                $builder = $this->addSelects($builder);

                return $entityManager // @phpstan-ignore-line
                    ->createNativeQuery($builder->getSQL(), $mapping)
                    ->setParameters([
                        'user' => $user->getId(),
                        'processor' => $processor->value,
                    ])
                    ->getResult();
            },
            3600
        );
    }

    abstract protected function addSelects(QueryBuilder $builder): QueryBuilder;

    abstract protected function getProcessor(): Processor;
}
