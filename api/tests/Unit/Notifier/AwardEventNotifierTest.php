<?php

declare(strict_types=1);

namespace App\Tests\Notifier;

use ApiPlatform\Metadata\IriConverterInterface;
use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\Part;
use App\Entity\User;
use App\Message\Awards\AwardEventMessageInterface;
use App\Message\Awards\NewEntryEventMessage;
use App\Message\Awards\NewPartEventMessage;
use App\Notifier\AwardEventNotifier;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[CoversClass(AwardEventNotifier::class)]
#[CoversClass(NewEntryEventMessage::class)]
#[CoversClass(NewPartEventMessage::class)]
#[UsesClass(EntryRepository::class)]
#[UsesClass(UserRepository::class)]
final class AwardEventNotifierTest extends KernelTestCase
{
    /**
     * @param array<string, string|null> $expectedPayload
     */
    #[DataProvider('provideAwardEvents')]
    public function testNotifySuccess(AwardEventMessageInterface $message, array $expectedPayload): void
    {
        /** @var TexterInterface&MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&MockObject $snsOptions */
        $snsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new AwardEventNotifier($texter, $serializer, $logger, $snsOptions);

        $payload = null;
        $texter
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (ChatMessage $message) use ($snsOptions, &$payload): bool {
                /** @var array<string, string|null> $payload */
                $payload = json_decode($message->getSubject(), true);

                return $message->getOptions() === $snsOptions;
            }))
        ;

        $logger->expects(self::never())->method('error');

        $notifier->notify($message);

        unset($payload['@id']);

        self::assertEqualsCanonicalizing($expectedPayload, $payload);
    }

    public function testNotifyFails(): void
    {
        /** @var TexterInterface&MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&MockObject $snsOptions */
        $snsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new AwardEventNotifier($texter, $serializer, $logger, $snsOptions);

        /** @var TransportExceptionInterface&MockObject $exception */
        $exception = $this->createMock(TransportExceptionInterface::class);
        $texter
            ->expects(self::once())
            ->method('send')
            ->willThrowException($exception)
        ;

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Failed to send SNS notification'))
        ;

        $user = self::getUser();
        /** @var Part */
        $part = $user->getParts()->first();

        $notifier->notify(new NewPartEventMessage($part));
    }

    /**
     * @return iterable<string, array{AwardEventMessageInterface, array<string, string|null>}>
     */
    public static function provideAwardEvents(): iterable
    {
        $container = self::getContainer();

        $entry = self::getEntry();
        $user = self::getUser();

        /** @var Part */
        $part = $user->getParts()->first();

        /** @var IriConverterInterface */
        $iriConverter = $container->get(IriConverterInterface::class);

        $userIri = $iriConverter->getIriFromResource($user);
        $entryIri = $iriConverter->getIriFromResource($entry);
        $partIri = $iriConverter->getIriFromResource($part);

        yield 'Entry Award Event' => [new NewEntryEventMessage($entry), [
            '@type' => 'NewEntryEventMessage',
            'entry' => $entryIri,
            'user' => $userIri,
        ]];

        yield 'Part Award Event' => [new NewPartEventMessage($part), [
            '@type' => 'NewPartEventMessage',
            'part' => $partIri,
            'user' => $userIri,
        ]];
    }

    public static function getUser(): User
    {
        $container = self::getContainer();

        /** @var UserRepository */
        $userRepository = $container->get(UserRepository::class);

        return $userRepository->findOneBy(['email' => UserFixtures::FIRST_USER]) ?? throw new \RuntimeException('User not found');
    }

    public static function getEntry(): Entry
    {
        $container = self::getContainer();

        /** @var EntryRepository */
        $entryRepository = $container->get(EntryRepository::class);

        return $entryRepository->createQueryBuilder('p') // @phpstan-ignore-line
            ->select('p')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
