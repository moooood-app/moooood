<?php

declare(strict_types=1);

namespace App\State\Provider\Metrics;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\MetricsIdentifierInterface;
use App\Entity\User;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\MetricsRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @implements ProviderInterface<object>
 */
final readonly class MetricsProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @return array<MetricsIdentifierInterface>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User */
        $user = $this->security->getUser();

        /** @var class-string */
        $class = $operation->getClass();
        $repository = $this->entityManager->getRepository($class);

        if (!$repository instanceof MetricsRepositoryInterface) {
            throw new \RuntimeException('Invalid repository');
        }

        if (!$context['request'] instanceof Request) {
            throw new \RuntimeException('No request');
        }

        /** @var InputBag<bool|float|int|string> */
        $query = $context['request']->query;

        /** @var string|null */
        $metricsType = $operation->getExtraProperties()[MetricsApiResource::EXTRA_PROPERTY_METRICS_TYPE] ?? null;

        if (null === $metricsType) {
            throw new \RuntimeException('No metrics type found, you must use the MetricsApiResource attribute');
        }

        $processor = Processor::tryFrom($metricsType);

        $metricsQuery = MetricsQuery::fromInputBag($query);
        if (null !== $processor) {
            $metricsQuery = $metricsQuery->withProcessor($processor);
        }

        $cacheKey = \sprintf(
            'user_%s-metrics_%s_%s',
            $user->getId(),
            $metricsType,
            $metricsQuery->groupingCriteria->value,
        );

        return $this->cache->get(
            $cacheKey,
            /** @return array<Complexity> */
            static function (ItemInterface $item) use ($user, $repository, $metricsQuery): array {
                $item->tag(\sprintf('user-metrics-%s', $user->getId()->toString()));

                return $repository->getMetrics(
                    $user,
                    $metricsQuery,
                );
            },
            3600,
        );
    }
}
