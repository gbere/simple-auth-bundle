<?php

declare(strict_types=1);

namespace Gbere\Security\Controller;

use Symfony\Component\Routing\Annotation\Route;

final class LogoutController
{
    /**
     * @Route("/logout", name="gbere_security_logout")
     */
    public function __invoke()
    {
    }
}
