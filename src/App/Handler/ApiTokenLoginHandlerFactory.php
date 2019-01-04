<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\ConfigException;
use App\Security\ContredanseProductAccess;
use App\Service\Token\TokenManager;
use Psr\Container\ContainerInterface;

class ApiTokenLoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiTokenLoginHandler
    {
        $userProvider  = $container->get(\App\Security\UserProviderInterface::class);
        $tokenService  = $container->get(TokenManager::class);
        $productAccess = $container->get(ContredanseProductAccess::class);

        $config = $container->get('config')['contredanse'] ?? null;
        if ($config === null) {
            throw new ConfigException("['contredanse'] config key is missing.");
        }
        if (!is_array($config['auth'] ?? false)) {
            throw new ConfigException("['contredanse']['auth'] config key is missing.");
        }

        return new ApiTokenLoginHandler($userProvider, $tokenService, $productAccess, $config['auth']);
    }
}
