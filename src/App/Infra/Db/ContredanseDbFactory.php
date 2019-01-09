<?php

declare(strict_types=1);

namespace App\Infra\Db;

use App\Exception\ConfigException;
use Psr\Container\ContainerInterface;

class ContredanseDbFactory
{
    public function __invoke(ContainerInterface $container): ContredanseDb
    {
        $config = $container->get('config')['contredanse'] ?? null;
        if ($config === null) {
            throw new ConfigException("['contredanse'] config key is missing.");
        }
        if (!is_array($config['db'] ?? false)) {
            throw new ConfigException("['contredanse']['db'] config key is missing.");
        }

        return new ContredanseDb(
            $config['db']
        );
    }
}
