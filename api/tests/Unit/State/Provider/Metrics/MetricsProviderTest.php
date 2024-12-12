<?php

declare(strict_types=1);

namespace App\Tests\State\Provider\Metrics;

use ApiPlatform\Metadata\HttpOperation;
use App\Entity\Metrics\MetricsIdentifierInterface;
use App\Entity\Metrics\Sentiment;
use App\Entity\Part;
use App\Entity\User;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\MetricsQuery;
use App\Repository\Metrics\MetricsRepositoryInterface;
use App\State\Provider\Metrics\MetricsProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @internal
 */
#[CoversClass(MetricsProvider::class)]
#[CoversClass(MetricsQuery::class)]
final class MetricsProviderTest extends TestCase
{
    public function testProvideReturnsMetrics(): void
    {
        // Arrange
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        $repository = $this->getValidRepository($entityManager);

        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $user->method('getId')->willReturn(new Uuid('550e8400-e29b-41d4-a716-446655440000'));
        $security->method('getUser')->willReturn($user);

        $entityManager->method('getRepository')->with(MetricsIdentifierInterface::class)->willReturn($repository);

        /** @var InputBag<string> */
        $queryBag = new InputBag([
            MetricsApiResource::GROUPING_FILTER_KEY => 'day',
            MetricsApiResource::FROM_DATE_FILTER_KEY => '2023-12-23',
        ]);

        /** @var Request&MockObject $request */
        $request = $this->createMock(Request::class);
        $request->query = $queryBag;

        $context = ['request' => $request];

        $operation = (new HttpOperation())
            ->withClass(MetricsIdentifierInterface::class)
            ->withExtraProperties([MetricsApiResource::EXTRA_PROPERTY_METRICS_TYPE => Processor::SENTIMENT->value])
        ;

        /** @var ItemInterface&MockObject $cacheItem */
        $cacheItem = $this->createMock(ItemInterface::class);
        $cacheItem->expects(self::once())->method('tag')->with('user-metrics-550e8400-e29b-41d4-a716-446655440000');

        $cache->method('get')->willReturnCallback(static function (string $key, callable $callback) use ($cacheItem) {
            return $callback($cacheItem);
        });

        $provider = new MetricsProvider($entityManager, $security, $cache);

        // Act
        $result = $provider->provide($operation, [], $context);

        // Assert
        self::assertCount(1, $result);
        self::assertSame('id', $result[0]->getId());
    }

    public function testProvideThrowsExceptionForInvalidRepository(): void
    {
        // Arrange
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        /** @var MetricsRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $operation = (new HttpOperation())->withClass('InvalidClass');

        $provider = new MetricsProvider($entityManager, $security, $cache);

        // Expect exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid repository');

        // Act
        $provider->provide($operation, [], []);
    }

    public function testProvideThrowsExceptionForMissingRequest(): void
    {
        // Arrange
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        $repository = $this->getValidRepository($entityManager);
        $entityManager->method('getRepository')->willReturn($repository);

        $operation = (new HttpOperation())->withClass('Class');

        $provider = new MetricsProvider($entityManager, $security, $cache);

        // Expect exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No request');

        // Act
        $provider->provide($operation, [], []);
    }

    public function testProvideThrowsExceptionForMissingMetricsType(): void
    {
        // Arrange
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        $repository = $this->getValidRepository($entityManager);
        $entityManager->method('getRepository')->willReturn($repository);

        $operation = (new HttpOperation())->withClass('Class');
        $operation->withExtraProperties([]);

        $provider = new MetricsProvider($entityManager, $security, $cache);

        /** @var InputBag<string> */
        $queryBag = new InputBag([]);

        /** @var Request&MockObject $request */
        $request = $this->createMock(Request::class);
        $request->query = $queryBag;

        $context = ['request' => $request];

        // Expect exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No metrics type found, you must use the MetricsApiResource attribute.');

        // Act
        $provider->provide($operation, [], $context);
    }

    /**
     * @return EntityRepository<object>
     */
    private function getValidRepository(EntityManagerInterface $entityManager): EntityRepository
    {
        return new class($entityManager, new ClassMetadata(Sentiment::class)) extends EntityRepository implements MetricsRepositoryInterface {
            public function getMetrics(User $user, MetricsQuery $query): array
            {
                return [new class implements MetricsIdentifierInterface {
                    public function getId(): string
                    {
                        return 'id';
                    }

                    public function getDate(): \DateTimeImmutable
                    {
                        return new \DateTimeImmutable();
                    }

                    public function getPart(): ?Part
                    {
                        return null;
                    }
                }];
            }
        };
    }
}
