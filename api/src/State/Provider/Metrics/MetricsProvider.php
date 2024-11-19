<?php

declare(strict_types=1);

namespace App\State\Provider\Metrics;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Repository\Metrics\MetricsRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProviderInterface<object>
 */
class MetricsProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
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

        /** @var string */
        $grouping = $context['request']->query->get('grouping');
        $groupingCriteria = GroupingCriteria::tryFrom($grouping);

        return $repository->getMetrics($user, $groupingCriteria); // @phpstan-ignore-line
    }
}
