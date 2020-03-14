<?php

declare(strict_types=1);

namespace Gbere\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class RegisterFormController extends AbstractController
{
    /**
     * @Route("/register", name="gbere_security_register")
     */
    public function __invoke(Request $request, UserPasswordEncoderInterface $passwordEncode): Response
    {
        return $this->render('frontend/register.html.twig');
    }
}
