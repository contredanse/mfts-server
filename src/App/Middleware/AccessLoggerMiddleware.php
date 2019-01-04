<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Infra\Log\AccessLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccessLoggerMiddleware implements MiddlewareInterface
{
    /**
     * @var AccessLogger
     */
    private $accessLogger;

    public function __construct(AccessLogger $accessLogger)
    {
        $this->accessLogger = $accessLogger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		$ip = $this->getClientIp($request);

		try {
			$this->accessLogger->log(
				AccessLogger::TYPE_LOGIN_SUCCESS,
				'test@example.org',
				$ip
			);
		} catch (\Throwable $e) {
			// Discard any error
			error_log('AccessLoggerMiddleware failure' . $e->getMessage());
		} finally {
			return $response;
		}
    }

    private function getClientIp(ServerRequestInterface $request): ?string
    {
        $serverParams = $request->getServerParams();
        if (isset($serverParams['REMOTE_ADDR'])) {
            return $serverParams['REMOTE_ADDR'];
        }

        return null;
    }
}
