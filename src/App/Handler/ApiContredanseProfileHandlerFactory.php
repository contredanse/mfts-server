<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\TokenManager;
use Psr\Container\ContainerInterface;

class ApiContredanseProfileHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiContredanseProfileHandler
    {
        $userProvider = $container->get(\App\Security\ContredanseUserProvider::class);
        $tokenService = $container->get(TokenManager::class);

        return new ApiContredanseProfileHandler($tokenService, $userProvider);
    }
}
