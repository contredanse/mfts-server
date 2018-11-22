<?php

declare(strict_types=1);
return [
    'contredanse' => [
        'db' => [
            'dsn'       => getenv('AUTH_DB_DSN'),
            'username'  => getenv('AUTH_DB_USER'),
            'password'  => getenv('AUTH_DB_PWD'),
            'charset'   => 'utf8',
            'driver_options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ],
        ]
    ]
];
