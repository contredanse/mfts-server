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
        if (mb_strlen($config['private_key'] ?: '') < 32) {
            throw new ConfigException("['tokenManager']['private_key'] config key is must be at least 32 chars long.");
        }

        $defaultExpiry = TokenManager::DEFAULT_EXPIRY;

		if (isset($config['default_expiry']) && (
				!is_numeric($config['default_expiry']) || $config['default_expiry'] < 0)) {
			throw new ConfigException("['tokenManager']['default_expiry'] must be numeric > 0");
		} else {
			$defaultExpiry = (int) $config['default_expiry'];
		}


        return new TokenManager($config['private_key'], $defaultExpiry);
    }
}
