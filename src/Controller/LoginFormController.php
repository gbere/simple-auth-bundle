<?php

declare(strict_types=1);

namespace Gbere\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginFormController extends AbstractController
{
    /**
     * @Route("/login", name="gbere_security_login")
     */
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        $lastAuthError = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/login.html.twig', [
            'last_username' => $lastUsername,
            'last_auth_error' => $lastAuthError,
        ]);
    }
}
