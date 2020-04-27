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

final class PasswordRequestController extends AbstractController
{
    /**
     * @Route("/password/request", name="gbere_auth_password_request")
     *
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(Request $request, Mailer $mailer, UserRepository $userRepository): Response
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
                $this->addFlash('info', sprintf('An email was sent to %s to restore the password', $email));

                return $this->redirectToRoute('gbere_auth_login');
            }
            $this->addFlash('warning', sprintf('The email %s isn\'t registered', $email));
        }

        return $this->render('@GbereSimpleAuth/frontend/password-request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
