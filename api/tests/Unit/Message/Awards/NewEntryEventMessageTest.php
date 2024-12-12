<?php

namespace App\Message\Awards;

use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\User;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
#[CoversClass(NewEntryEventMessage::class)]
#[UsesClass(EntryRepository::class)]
#[UsesClass(UserRepository::class)]
final class NewEntryEventMessageTest extends KernelTestCase
{
    public function testNewInstance(): void
    {
        $entry = (new Entry())->setUser($user = new User());
        $message = new NewEntryEventMessage($entry);
        self::assertSame($entry, $message->getEntry());
        self::assertSame($user, $message->getUser());
    }

    public function testNormalizedMessageOnlyContainsIris(): void
    {
        self::bootKernel();

        /** @var UserRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User */
        $user = $userRepository->findOneBy(['email' => UserFixtures::FIRST_USER]);
        /** @var EntryRepository */
        $entryRepository = self::getContainer()->get(EntryRepository::class);

        /** @var Entry */
        $entry = $entryRepository->createQueryBuilder('p')
            ->select('p')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        $message = new NewEntryEventMessage($entry);
        $normalizer = self::getContainer()->get(NormalizerInterface::class);

        /** @var array<string, string> $normalizedMessage */
        $normalizedMessage = $normalizer->normalize($message, 'jsonld', [
            AbstractNormalizer::GROUPS => [AwardEventMessageInterface::SERIALIZATION_GROUP_SNS],
            'jsonld_has_context' => false,
        ]);

        self::assertArrayHasKey('@id', $normalizedMessage);
        unset($normalizedMessage['@id']);

        self::assertSame(
            [
                '@type' => 'NewEntryEventMessage',
                'entry' => \sprintf('/api/entries/%s', $entry->getId()->toString()),
                'user' => \sprintf('/api/users/%s', $user->getId()->toString()),
            ],
            $normalizedMessage,
        );
    }
}
