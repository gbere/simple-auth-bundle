<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Repository\UserRepository;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PasswordRequestController extends AbstractController
{
    /**
     * @Route("/password/request", name="simple_auth_password_request")
     *
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(Request $request, Mailer $mailer, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $builder = $this->createFormBuilder()->add('email', EmailType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);
            if (null !== $user) {
                $user->generateToken();
                $user->setPasswordRequestAt(new DateTime());
                $userRepository->persistAndFlush($user);
                $mailer->sendPasswordResetMessage($user);
                $this->addFlash('info', $translator->trans('flash.restore_password', ['email' => $email], 'SimpleAuthBundle'));

                return $this->redirectToRoute('simple_auth_login');
            }
            $this->addFlash('warning', $translator->trans('flash.email_not_registered', ['email' => $email], 'SimpleAuthBundle'));
        }

        return $this->render('@GbereSimpleAuth/frontend/password-request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
