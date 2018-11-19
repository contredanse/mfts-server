<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;

class AuthTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container) : AuthTokenHandler
    {
        $userProvider = $container->get(\App\Security\ContredanseUserProvider::class);
        return new AuthTokenHandler($userProvider);
    }
}
