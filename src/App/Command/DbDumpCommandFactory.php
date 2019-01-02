<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Db\ContredanseDb;
use Psr\Container\ContainerInterface;

class DbDumpCommandFactory
{
    public function __invoke(ContainerInterface $container): DbDumpCommand
    {
        return new DbDumpCommand(
            $container->get(ContredanseDb::class)
        );
    }
}
