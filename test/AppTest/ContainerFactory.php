<?php declare(strict_types=1);

namespace AppTest;

class ContainerFactory {

    static function getContainer(): \Psr\Container\ContainerInterface {
        chdir(dirname(__DIR__, 2));
        $container = require __DIR__ . '/../../config/container.php';
        return $container;

    }

}
