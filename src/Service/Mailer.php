<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Service;

use Gbere\SimpleAuth\Entity\User;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    /** @var MailerInterface */
    private $mailer;
    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmRegistrationMessage(User $user): void
    {
        $this->mailer->send((new NotificationEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Confirm registration')
            ->htmlTemplate('emails/confirm-registration.html.twig')
            ->context(['token' => $user->getConfirmationToken()])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendWelcomeMessage(User $user): void
    {
        $this->mailer->send((new NotificationEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Welcome')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context(['token' => $user->getConfirmationToken()])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetMessage(User $user): void
    {
        $this->mailer->send((new NotificationEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Password request')
            ->htmlTemplate('emails/password-reset.html.twig')
            ->context(['token' => $user->getConfirmationToken()])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetNotificationMessage(User $user): void
    {
        $this->mailer->send((new NotificationEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Password reset notification')
            ->htmlTemplate('emails/password-reset-notification.html.twig')
        );
    }

    private function getSenderEmail(): string
    {
        return $this->parameterBag->get('email.sender');
    }
}
