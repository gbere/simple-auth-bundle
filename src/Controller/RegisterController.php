<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="gbere_auth_register")
     */
    public function __invoke(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword() ?? ''));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('gbere_auth_login');
        }

        return $this->render('frontend/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
