<?php

declare(strict_types=1);
return [
    'contredanse' => [
        'db' => [
        	'driver'    => getenv('AUTH_DB_DRIVER'),
            'host'      => getenv('AUTH_DB_HOST'),
			'dbname'    => getenv('AUTH_DB_DBNAME'),
			'port'      => (int) getenv('AUTH_DB_PORT'),
            'username'  => getenv('AUTH_DB_USER'),
            'password'  => getenv('AUTH_DB_PWD'),
            'charset'   => 'utf8',
            'driver_options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::MYSQL_ATTR_COMPRESS => true,
            ],
        ],

		'auth' => [
			// Token expiry for ApiAuthTokenHandler
			'token_expiry' => (int) (getenv('JWT_TOKEN_DEFAULT_EXPIRY') ?: 3600)
		]
    ]
];
