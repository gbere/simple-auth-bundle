<?php

declare(strict_types=1);

namespace Gbere\Security\Command\Exception;

class InvalidRolePatternException extends \Exception
{
    public function __construct(string $allowedPattern)
    {
        parent::__construct(sprintf('The role name must match with the regular expression %s', $allowedPattern));
    }
}
