<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Security\UserProviderInterface;
use Psr\Container\ContainerInterface;

class AuthManagerFactory
{
    public function __invoke(ContainerInterface $container): AuthManager
    {
        return new AuthManager(
            $container->get(UserProviderInterface::class)
        );
    }
}
