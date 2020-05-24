<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Service;

use Gbere\SimpleAuth\Bridge\Mime\TemplatedEmail;
use Gbere\SimpleAuth\Model\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Mailer
{
    /** @var ParameterBagInterface */
    private $parameterBag;
    /** @var MailerInterface */
    private $mailer;
    /** @var RouterInterface */
    private $router;

    public function __construct(ParameterBagInterface $parameterBag, MailerInterface $mailer, RouterInterface $router)
    {
        $this->parameterBag = $parameterBag;
        $this->mailer = $mailer;
        $this->router = $router;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmRegistrationMessage(UserInterface $user): void
    {
        $this->mailer->send((new TemplatedEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Confirm registration')
            ->context([
                'content' => 'Please click the next link to confirm registration',
                'action_url' => $this->generateUrl('simple_auth_confirm_registration', ['token' => $user->getConfirmationToken()]),
                'action_text' => 'Confirm registration',
            ])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendWelcomeMessage(UserInterface $user): void
    {
        $this->mailer->send((new TemplatedEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Welcome')
            ->context(['content' => 'Your registration was successfully completed'])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetMessage(UserInterface $user): void
    {
        $this->mailer->send((new TemplatedEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Password request')
            ->context([
                'content' => 'Please, click the next link to reset your password',
                'action_url' => $this->generateUrl('simple_auth_password_reset', ['token' => $user->getConfirmationToken()]),
                'action_text' => 'Reset password',
            ])
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetNotificationMessage(UserInterface $user): void
    {
        $this->mailer->send((new TemplatedEmail())
            ->from($this->getSenderEmail())
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Password reset notification')
            ->context([
                'content' => 'Your password was successfully updated',
            ])
        );
    }

    private function getSenderEmail(): Address
    {
        return new Address(
            $this->parameterBag->get('simple_auth_sender_email'),
            $this->parameterBag->get('simple_auth_sender_name') ?? ''
        );
    }

    private function generateUrl(string $name, array $parameters = null): string
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
