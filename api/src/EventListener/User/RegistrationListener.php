<?php

namespace App\EventListener\User;

use App\Entity\User;
use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class RegistrationListener
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    #[AsEventListener(event: HWIOAuthEvents::REGISTRATION_COMPLETED)]
    public function onRegistrationComplete(FilterUserResponseEvent $event): void
    {
        if (!($user = $event->getUser()) instanceof User) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName()))
            ->subject('Welcome to Moooood!')
            ->htmlTemplate('emails/users/welcome.html.twig')
            ->context([
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastname(),
            ])
        ;

        $this->mailer->send($email);
    }
}
