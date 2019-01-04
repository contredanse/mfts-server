<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Infra\Log\AccessLogger;
use Psr\Container\ContainerInterface;

class AccessLoggerMiddelwareFactory
{
    public function __invoke(ContainerInterface $container): AccessLoggerMiddleware
    {
        return new AccessLoggerMiddleware(
            $container->get(AccessLogger::class)
        );
    }
}
