<?php

declare(strict_types=1);

namespace App\Tests\OAuth;

use App\Entity\User;
use App\OAuth\OAuthRegistrationHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use PHPUnit\Framework\Attributes\CoversClass;
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
    public function testProcessSuccess(): void
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
        /** @var User|null $createdUser */
        $createdUser = null;
        $form->expects(self::once())->method('setData')->with(self::callback(static function (User $user) use (&$createdUser) {
            $createdUser = $user;

            return
                'test@test.com' === $user->getEmail()
                && 'test@test.com' === $user->getUserIdentifier()
                && 'John' === $user->getFirstName()
                && 'Doe' === $user->getLastName();
        }));
        $form->expects(self::once())->method('handleRequest')->with($request);
        $form->expects(self::once())->method('isSubmitted')->willReturn(true);
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $form->expects(self::once())->method('get')->with('plainPassword')->willReturn($form);
        $form->expects(self::once())->method('getData')->willReturn('password');

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $handler = new OAuthRegistrationHandler($userPasswordHasher);

        self::assertTrue($handler->process($request, $form, $userResponse));

        self::assertNotNull($createdUser);
        self::assertNotEmpty($createdUser->getPassword());
    }
}
