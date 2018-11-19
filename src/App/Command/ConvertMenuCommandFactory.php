<?php declare(strict_types=1);

namespace App\Command;

use App\Util\MenuConvert;
use Psr\Container\ContainerInterface;

class ConvertMenuCommandFactory
{
    public function __invoke(ContainerInterface $container): ConvertMenuCommand
    {

        return new ConvertMenuCommand(
            $container->get(MenuConvert::class)
        );
    }
}
