<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\ContredanseDb;
use Psr\Container\ContainerInterface;

class ApiContredanseStatusHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiContredanseStatusHandler
    {
        $contredanseDb = $container->get(ContredanseDb::class);

        return new ApiContredanseStatusHandler($contredanseDb);
    }
}
