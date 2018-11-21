<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\ConfigException;
use App\Service\TokenManager;
use Psr\Container\ContainerInterface;

class AuthTokenMiddlewareFactory
{
    /**
     * @throws ConfigException
     */
    public function __invoke(ContainerInterface $container): AuthTokenMiddleware
    {
        $config = $container->get('config')['token_manager'] ?? null;
        if ($config === null) {
            throw new ConfigException("['token_manager'] config key is missing.");
        }

        $options = [
            AuthTokenMiddleware::OPTION_ALLOW_INSECURE_HTTP => $config['allow_insecure_http'],
            AuthTokenMiddleware::OPTION_RELAXED_HOSTS       => $config['relaxed_hosts']
        ];

        return new AuthTokenMiddleware(
            $container->get(TokenManager::class),
            $options
        );
    }
}
