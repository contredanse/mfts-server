<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\TokenManager;
use Psr\Container\ContainerInterface;

class ApiAuthTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiAuthTokenHandler
    {
        $userProvider = $container->get(\App\Security\UserProviderInterface::class);
        $tokenService = $container->get(TokenManager::class);

        return new ApiAuthTokenHandler($userProvider, $tokenService);
    }
}
