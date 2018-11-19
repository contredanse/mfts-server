<?php declare(strict_types=1);

namespace App\Command;

use App\Util\MenuConvert;
use Psr\Container\ContainerInterface;

class ConvertVideoCommandFactory
{
    public function __invoke(ContainerInterface $container): ConvertVideoCommand
    {

        return new ConvertVideoCommand(
            $container->get(MenuConvert::class)
        );
    }
}
