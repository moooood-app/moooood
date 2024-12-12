<?php

namespace App\Message\Awards;

use App\DataFixtures\UserFixtures;
use App\Entity\Part;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
#[CoversClass(NewPartEventMessage::class)]
#[UsesClass(UserRepository::class)]
final class NewPartEventMessageTest extends KernelTestCase
{
    public function testNewInstance(): void
    {
        $part = (new Part())->setUser($user = new User());
        $message = new NewPartEventMessage($part);
        self::assertSame($part, $message->getPart());
        self::assertSame($user, $message->getUser());
    }

    public function testNormalizedMessageOnlyContainsIris(): void
    {
        self::bootKernel();

        /** @var UserRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User */
        $user = $userRepository->findOneBy(['email' => UserFixtures::FIRST_USER]);
        /** @var Part */
        $part = $user->getParts()->first();

        $message = new NewPartEventMessage($part);
        $normalizer = self::getContainer()->get(NormalizerInterface::class);

        /** @var array<mixed> */
        $normalizedMessage = $normalizer->normalize($message, 'jsonld', [
            AbstractNormalizer::GROUPS => [AwardEventMessageInterface::SERIALIZATION_GROUP_SNS],
            'jsonld_has_context' => false,
        ]);

        self::assertArrayHasKey('@id', $normalizedMessage);
        unset($normalizedMessage['@id']);

        self::assertSame(
            [
                '@type' => 'NewPartEventMessage',
                'part' => \sprintf('/api/parts/%s', $part->getId()->toString()),
                'user' => \sprintf('/api/users/%s', $user->getId()->toString()),
            ],
            $normalizedMessage,
        );
    }
}
