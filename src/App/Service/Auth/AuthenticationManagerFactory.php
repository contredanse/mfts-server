<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Security\UserProviderInterface;
use Psr\Container\ContainerInterface;

class AuthenticationManagerFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationManager
    {
        return new AuthenticationManager(
            $container->get(UserProviderInterface::class)
        );
    }
}
