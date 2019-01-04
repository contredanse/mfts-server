<?php

declare(strict_types=1);

namespace App;

/**
 * The configuration provider for the App module.
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array.
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies.
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                Handler\HomePageHandler::class                      => Handler\HomePageHandlerFactory::class,
                Handler\ApiTokenLoginHandler::class                 => Handler\ApiTokenLoginHandlerFactory::class,
                Handler\ApiTokenValidateHandler::class              => Handler\ApiTokenValidateHandlerFactory::class,
                Handler\ApiContredanseStatusHandler::class		    => Handler\ApiContredanseStatusHandlerFactory::class,
                Handler\ApiContredanseProfileHandler::class		    => Handler\ApiContredanseProfileHandlerFactory::class,

                // Middleware
                Middleware\AuthTokenMiddleware::class      => Middleware\AuthTokenMiddlewareFactory::class,
				Middleware\AccessLoggerMiddleware::class   => Middleware\AccessLoggerMiddelwareFactory::class,
                \Tuupola\Middleware\CorsMiddleware::class  => Middleware\ApiCorsMiddlewareFactory::class,

                // Security
                // From configured interface
                Security\UserProviderInterface::class      => Security\ContredanseUserProviderFactory::class,
                // Explicit
                Security\ContredanseUserProvider::class    => Security\ContredanseUserProviderFactory::class,
                Security\ContredanseProductAccess::class   => Security\ContredanseProductAccessFactory::class,

                // Service
                Service\Db\ContredanseDb::class           => Service\Db\ContredanseDbFactory::class,
                Service\Token\TokenManager::class         => Service\Token\TokenManagerFactory::class,
                Service\Auth\AuthenticationManager::class => Service\Auth\AuthenticationManagerFactory::class,

                // Infrastructure
                Infra\Log\AccessLogger::class => Infra\Log\AccessLoggerFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration.
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }
}
