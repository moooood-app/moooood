<?php

declare(strict_types=1);

namespace App\Tests\OAuth;

use App\Entity\User;
use App\OAuth\OAuthRegistrationHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @internal
 */
#[CoversClass(OAuthRegistrationHandler::class)]
#[CoversClass(User::class)]
final class OAuthRegistrationHandlerTest extends KernelTestCase
{
    #[DataProvider('provideSuccessOrFailure')]
    public function testProcessSuccess(bool $submitted, bool $valid): void
    {
        $request = new Request();

        $userResponse = new PathUserResponse();
        $userResponse->setPaths([
            'email' => 'email',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
        ]);
        $userResponse->setData([
            'email' => 'test@test.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);

        /** @var FormInterface<User>&MockObject $form */
        $form = $this->createMock(FormInterface::class);
        /** @var User|null $submittedUser */
        $submittedUser = null;
        $form->expects(self::once())->method('setData')->with(self::callback(static function (User $user) use (&$submittedUser) {
            $submittedUser = $user;

            return
                'test@test.com' === $user->getEmail()
                && 'test@test.com' === $user->getUserIdentifier()
                && 'John' === $user->getFirstName()
                && 'Doe' === $user->getLastName();
        }));
        $form->expects(self::once())->method('handleRequest')->with($request);
        $form->expects(self::once())->method('isSubmitted')->willReturn($submitted);
        $form->expects(self::exactly((int) $submitted))->method('isValid')->willReturn($valid);
        $form->expects(self::exactly((int) ($submitted && $valid)))->method('get')->with('plainPassword')->willReturn($form);
        $form->expects(self::exactly((int) ($submitted && $valid)))->method('getData')->willReturn('password');

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $handler = new OAuthRegistrationHandler($userPasswordHasher);

        self::assertSame($submitted && $valid, $handler->process($request, $form, $userResponse));

        if ($submitted && $valid) {
            self::assertNotNull($submittedUser);
            self::assertNotEmpty($submittedUser->getPassword());
        }
    }

    /**
     * @return iterable<array{0: bool, 1: bool}>
     */
    public static function provideSuccessOrFailure(): iterable
    {
        yield 'Form is not submitted' => [false, false];
        yield 'Form is not valid' => [true, false];
        yield 'Form is submitted and valid' => [true, true];
    }
}
