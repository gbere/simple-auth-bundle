<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Symfony\Component\Routing\Annotation\Route;

final class LogoutController
{
    /**
     * @Route("/logout", name="gbere_auth_logout")
     */
    public function __invoke(): void
    {
    }
}
