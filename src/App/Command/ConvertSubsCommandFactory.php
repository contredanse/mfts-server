<?php declare(strict_types=1);

namespace App\Command;

use App\Util\MenuConvert;
use Psr\Container\ContainerInterface;

class ConvertSubsCommandFactory
{
    public function __invoke(ContainerInterface $container): ConvertSubsCommand
    {

        return new ConvertSubsCommand(
            $container->get(MenuConvert::class)
        );
    }
}
