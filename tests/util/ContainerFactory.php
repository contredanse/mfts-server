<?php

declare(strict_types=1);

namespace AppTest\Util;

class ContainerFactory
{
    public static function getContainer(): \Psr\Container\ContainerInterface
    {
        chdir(\dirname(__DIR__, 3));
        $container = require __DIR__ . '/../../config/container.php';

        return $container;
    }
}
