<?php

declare(strict_types=1);

namespace App\State\Provider\Metrics;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Metrics\MetricsIdentifierInterface;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\MetricsRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @implements ProviderInterface<object>
 */
final readonly class MetricsProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private CacheInterface $cache,
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

        $processor = $operation->getExtraProperties()[MetricsApiResource::EXTRA_PROPERTY_METRICS_PROCESSOR] ?? null;
        if (!$processor instanceof Processor) {
            throw new \RuntimeException('No processor defined! This provider is only compatible with data produced by processors. Please implement your own provider.');
        }

        /** @var string */
        $grouping = $context['request']->query->get('grouping');
        $groupingCriteria = GroupingCriteria::from($grouping);

        $cacheKey = \sprintf(
            '%s_metrics_%s_%s',
            $processor->value,
            $user->getId(),
            $groupingCriteria->value
        );

        return $this->cache->get( // @phpstan-ignore-line
            $cacheKey,
            /** @return array<Complexity> */
            static function () use ($user, $groupingCriteria, $repository, $processor): array {
                return $repository->getMetrics($user, $groupingCriteria, $processor);
            },
            3600,
        );
    }
}
