<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\Token\TokenManager;
use Psr\Container\ContainerInterface;

class ApiTokenValidateHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiTokenValidateHandler
    {
        $tokenService = $container->get(TokenManager::class);

        return new ApiTokenValidateHandler($tokenService);
    }
}
