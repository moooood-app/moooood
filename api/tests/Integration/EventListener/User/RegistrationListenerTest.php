<?php

namespace App\Tests\Integration\EventListener\User;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\EventListener\User\RegistrationListener;
use App\Messenger\Serializer\AwardEventDecoder;
use App\Messenger\Serializer\ProcessorOutputDecoder;
use App\Repository\UserRepository;
use App\Schedule;
use App\Scheduler\AwardsScheduler;
use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Security\Core\User\UserInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(RegistrationListener::class)]
#[UsesClass(ProcessorOutputDecoder::class)]
#[UsesClass(AwardEventDecoder::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(AwardsScheduler::class)]
#[UsesClass(Schedule::class)]
final class RegistrationListenerTest extends KernelTestCase
{
    use InteractsWithMessenger;

    public function testEmailIsSentOnRegistration(): void
    {
        /** @var RegistrationListener */
        $listener = self::getContainer()->get(RegistrationListener::class);

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $event = new FilterUserResponseEvent($user, new Request(), new Response());
        $listener->onRegistrationComplete($event);

        $queue = $this->transport('mailer')->queue();
        $queue->assertCount(1);
        $queue->assertContains(SendEmailMessage::class, 1);

        $queue->first(static function (SendEmailMessage $e) use ($user): bool {
            if (!($message = $e->getMessage()) instanceof TemplatedEmail) {
                return false;
            }

            if (!$recipient = $message->getTo()[0] ?? null) {
                return false;
            }

            return $recipient->getAddress() === $user->getEmail()
                && $recipient->getName() === $user->getFirstName()
                && 'Welcome to Moooood!' === $message->getSubject();
        });
    }

    public function testListenerOnlyAppliesToUserObjects(): void
    {
        /** @var RegistrationListener */
        $listener = self::getContainer()->get(RegistrationListener::class);

        $event = new FilterUserResponseEvent($this->createMock(UserInterface::class), new Request(), new Response());
        $listener->onRegistrationComplete($event);

        $queue = $this->transport('mailer')->queue();
        $queue->assertCount(0);
    }
}
