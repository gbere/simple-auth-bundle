<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Security;

class Constant
{
    public const PROVIDER_NAME = 'gbere_auth_main_provider';
    public const FIREWALL_NAME = 'gbere_auth_main_firewall';
    public const TESTING_ROUTES = [
        ['path' => '^/gbere-auth-test-role-admin', 'role' => 'ROLE_ADMIN'],
        ['path' => '^/gbere-auth-test-role-user', 'role' => 'ROLE_USER'],
    ];
}
