<?php

declare(strict_types=1);

namespace App\Infra\Log;

use App\Exception\ConfigException;
use Psr\Container\ContainerInterface;

class AccessLoggerFactory
{
    public function __invoke(ContainerInterface $container): AccessLogger
    {
        $entityManager = $container->get('doctrine.entity_manager.orm_default') ?? null;

        if ($entityManager === null) {
            throw new ConfigException('EntityManager / doctrine.entity_manager.orm_default is not registered.');
        }

        return new AccessLogger(
            $entityManager
        );
    }
}
