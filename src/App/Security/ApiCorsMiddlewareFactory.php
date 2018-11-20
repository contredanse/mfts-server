<?php

declare(strict_types=1);

namespace App\Security;

use Psr\Container\ContainerInterface;
use Tuupola\Middleware\CorsMiddleware;
use Zend\ProblemDetails\ProblemDetailsResponseFactory;

class ApiCorsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CorsMiddleware
    {
        $problemDetailsResponseFactory = $container->get(ProblemDetailsResponseFactory::class);

        return new CorsMiddleware([
            'origin'         => ['*'],
            'methods'        => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow'  => ['Authorization', 'If-Match', 'If-Unmodified-Since', 'Content-Type'],
            'headers.expose' => ['Etag'],
            'credentials'    => true,
            'cache'          => 0,
            'error'          => function ($request, $response, $arguments) use ($problemDetailsResponseFactory) {
                //return $this->error($request, $response, $arguments);
                return $problemDetailsResponseFactory->createResponse(
                    $request,
                    401,
                    '',
                    $arguments['message'],
                    '',
                    []
                );
            }
        ]);
    }
}
