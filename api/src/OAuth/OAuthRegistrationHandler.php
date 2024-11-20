<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\User;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class OAuthRegistrationHandler implements RegistrationFormHandlerInterface
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    /**
     * @param FormInterface<User> $form
     */
    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
    {
        $user = new User();
        $user->setEmail((string) $userInformation->getEmail());
        $user->setFirstName((string) $userInformation->getFirstName());
        $user->setLastName((string) $userInformation->getLastName());

        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            \assert(\is_string($form->get('plainPassword')->getData()));
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData(),
                )
            );

            return true;
        }

        return false;
    }
}
