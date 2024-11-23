<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Entity\User;
use App\Enum\Processor;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(EntryRepository::class)]
#[CoversClass(Entry::class)]
#[CoversClass(EntryMetadata::class)]
#[CoversClass(User::class)]
#[UsesClass(UserRepository::class)]
final class EntryRepositoryTest extends KernelTestCase
{
    public function testRemoveExitingMetadata(): void
    {
        $container = self::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $userRepository = $container->get(UserRepository::class);

        /** @var User */
        $user = $userRepository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $entryRepository = $container->get(EntryRepository::class);

        $entry = new Entry();
        $entry
            ->setUser($user)
            ->setContent('Hello world')
        ;
        $entityManager->persist($entry);

        $metadataToBeRemoved = new EntryMetadata();
        $metadataToBeRemoved
            ->setProcessor(Processor::COMPLEXITY)
            ->setEntry($entry)
            ->setMetadata([])
        ;
        $entityManager->persist($metadataToBeRemoved);

        $metadataNotToBeRemoved = new EntryMetadata();
        $metadataNotToBeRemoved
            ->setProcessor(Processor::SENTIMENT)
            ->setEntry($entry)
            ->setMetadata([])
        ;
        $entityManager->persist($metadataNotToBeRemoved);

        $entry
            ->addMetadata($metadataToBeRemoved)
            ->addMetadata($metadataNotToBeRemoved)
        ;

        $entityManager->flush();

        $entryRepository->removeExistingMetadataForProcessor($entry, Processor::COMPLEXITY);

        $entityManager->refresh($entry);

        self::assertCount(1, $entry->getMetadata());
        self::assertSame($metadataNotToBeRemoved, $entry->getMetadata()->first());
    }
}
