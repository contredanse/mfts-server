<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {

	// The famous temporary home page :)

    $app->get('/', App\Handler\HomePageHandler::class, 'home');

    // A ping monitoring addess

    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');


    // Routes for JWT auth and validation

    $app->route('/api/auth/token',
        [BodyParamsMiddleware::class, App\Handler\ApiTokenLoginHandler::class],
        ['POST'],
        'api.auth.token'
    );

	$app->route('/api/auth/validate',
		[BodyParamsMiddleware::class, App\Handler\ApiTokenValidateHandler::class],
		['POST'],
		'api.auth.validate'
	);


    // To monitor link with contredanse database

	$app->get('/api/contredanse_status', App\Handler\ApiContredanseStatusHandler::class, 'api.contredanse.status');

	// The profile data (protected by AuthTokenMiddleware

    $app->get('/api/v1/profile',
		[\App\Middleware\AuthTokenMiddleware::class, \App\Handler\ApiContredanseProfileHandler::class],
		'api.v1.profile'
	);
};
