<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Gbere\SimpleAuth\Repository\UserRepository;
use Gbere\SimpleAuth\Security\LoginFormAuthenticator;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

final class ConfirmRegistrationController extends AbstractController
{
    /**
     * @Route("/register/confirmation/{token}", name="gbere_auth_confirm_registration")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(
        string $token,
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        Mailer $mailer,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);
        if (null === $user) {
            $this->addFlash('danger', 'The token is invalid');

            return $this->redirectToRoute('gbere_auth_login');
        }
        $user->hasEnabled(true);
        $user->setConfirmationToken(null);
        $userRepository->persistAndFlush($user);
        $this->addFlash('success', 'The user has been successfully enabled');
        $mailer->sendWelcomeMessage($user);

        // TODO: Auto login after validate?
        // return $guardHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'gbere_main_firewall');

        return $this->redirectToRoute('gbere_auth_login');
    }
}
