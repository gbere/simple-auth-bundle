<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Exception;
use Gbere\SimpleAuth\Entity\UserInterface;
use Gbere\SimpleAuth\Form\RegisterType;
use Gbere\SimpleAuth\Repository\UserRepository;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="simple_auth_register")
     *
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function __invoke(Request $request, UserRepository $userRepository, Mailer $mailer, TranslatorInterface $translator): Response
    {
        $user = $userRepository->createUser();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserInterface $user */
            $user = $form->getData();
            $user->setPassword($userRepository->encodePassword($user->getPassword() ?? ''));
            if ((bool) $this->getParameter('simple_auth_confirm_registration')) {
                $user->generateToken();
                $mailer->sendConfirmRegistrationMessage($user);
                $this->addFlash('info', $translator->trans('fash.confirm_registration', ['%email%' => $user->getEmail()], 'SimpleAuthBundle'));
            } else {
                $user->hasEnabled(true);
            }
            $userRepository->persistAndFlush($user);

            return $this->redirectToRoute('simple_auth_login');
        }

        return $this->render('@GbereSimpleAuth/frontend/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
