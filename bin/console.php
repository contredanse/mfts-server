<?php

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$container = require $basePath . '/config/container.php';
$application = new Application('Application console');

$commands = $container->get('config')['console']['commands'];
foreach ($commands as $command) {
    $application->add($container->get($command));
}

$application->run();
