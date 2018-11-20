<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ConfigException;
use Psr\Container\ContainerInterface;

class TokenManagerFactory
{
    public function __invoke(ContainerInterface $container): TokenManager
    {
        $config = $container->get('config')['token_manager'] ?? null;
        if ($config === null) {
            throw new ConfigException("['token_manager'] config key is missing.");
        }
        if (mb_strlen($config['privateKey'] ?? '') < 32) {
            throw new ConfigException("['tokenManager']['privateKey'] config key is must be at least 32 chars long.");
        }

        return new TokenManager($config['privateKey']);
    }
}
