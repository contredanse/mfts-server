<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\ConfigException;
use App\Infra\Db\ContredanseDb;
use Psr\Container\ContainerInterface;

class ContredanseUserProviderFactory
{
    /**
     * @throws ConfigException
     */
    public function __invoke(ContainerInterface $container): UserProviderInterface
    {
        $contredanseDb = $container->get(ContredanseDb::class);

        return new ContredanseUserProvider(
            $contredanseDb->getPdoAdapter()
        );
    }
}
