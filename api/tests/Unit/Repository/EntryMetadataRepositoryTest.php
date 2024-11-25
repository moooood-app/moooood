<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Entity\User;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use App\Repository\EntryMetadataRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(EntryMetadataRepository::class)]
#[CoversClass(ProcessorOutputMessage::class)]
#[UsesClass(UserRepository::class)]
final class EntryMetadataRepositoryTest extends KernelTestCase
{
    public function testCreateMetadataFromProcessorOutput(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        /** @var User */
        $user = $userRepository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $entry = new Entry();
        $entry
            ->setContent('Test Entry')
            ->setUser($user)
        ;

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $entityManager->persist($entry);

        $processorOutputMessage = new ProcessorOutputMessage($entry, ['key' => 'value'], Processor::KEYWORDS);

        /** @var EntryMetadataRepository $repository */
        $repository = $container->get(EntryMetadataRepository::class);
        $repository->createMetadataFromProcessorOutput($entry, $processorOutputMessage);

        $entityManager->refresh($entry);

        self::assertCount(1, $entry->getMetadata());

        /** @var EntryMetadata $metadata */
        $metadata = $entry->getMetadata()->first();

        self::assertSame($entry, $metadata->getEntry());
        self::assertSame(Processor::KEYWORDS, $metadata->getProcessor());
        self::assertSame(['key' => 'value'], $metadata->getMetadata());
    }
}
