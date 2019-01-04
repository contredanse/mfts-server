<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\Token\Exception\TokenValidationExceptionInterface;
use App\Service\Token\TokenManager;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class AuthTokenMiddleware implements MiddlewareInterface
{
    public const DEFAULT_OPTIONS = [
        self::OPTION_ALLOW_INSECURE_HTTP => false,
        self::OPTION_RELAXED_HOSTS       => [],
        self::OPTION_HTTP_HEADER         => 'Authorization',
        self::OPTION_HTTP_HEADER_PREFIX  => 'Bearer',
    ];

    public const OPTION_ALLOW_INSECURE_HTTP = 'allow_insecure_http';
    public const OPTION_RELAXED_HOSTS       = 'relaxed_hosts';

    /**
     * @var string
     */
    public const OPTION_HTTP_HEADER = 'httpHeader';

    /**
     * @var string
     */
    public const OPTION_HTTP_HEADER_PREFIX = 'httpHeaderPrefix';

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @var TokenManager
     */
    private $tokenManager;

    /**
     * @param mixed[] $options
     */
    public function __construct(TokenManager $tokenManager, array $options = [])
    {
        $this->tokenManager = $tokenManager;
        $this->options      = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Check for secure scheme (with exception of relaxed_hosts)
        $scheme = mb_strtolower($request->getUri()->getScheme());

        if ($this->options['allow_insecure_http'] !== true && $scheme !== 'https') {
            $host          = $request->getUri()->getHost();
            $relaxed_hosts = (array) $this->options['relaxed_hosts'];
            if (!in_array($host, $relaxed_hosts, true)) {
                throw new Exception\InsecureSchemeException(sprintf(
                    'Insecure scheme (%s) denied by configuration.',
                    $scheme
                ));
            }
        }

        // 2. Fetch token from server request

        $plainToken = $this->getTokenFromRequest($request);

        // 3. Validate the token
        if ($plainToken !== null) {
            try {
                $token = $this->tokenManager->getValidatedToken($plainToken);

                return $handler->handle($request->withAttribute(self::class, $token));
            } catch (TokenValidationExceptionInterface $e) {
                return (new JsonResponse([
                    'valid'  => false,
                    'reason' => $e->getReason(),
                ]))->withStatus($e->getStatusCode());
            } catch (\Throwable $e) {
                return (new JsonResponse([
                    'valid'  => false,
                    'reason' => 'Unknown reason',
                ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
            }
        }

        return (new JsonResponse([
            'valid'  => false,
            'reason' => 'No token provided',
        ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
    }

    public function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $headerPrefix = $this->options[self::OPTION_HTTP_HEADER_PREFIX];
        $headers      = $request->getHeader($this->options[self::OPTION_HTTP_HEADER]);
        $tokenString  = null;
        foreach ($headers as $header) {
            if ($headerPrefix !== '') {
                if (mb_strpos($header, $headerPrefix) === 0) {
                    $tokenString = trim(str_replace($headerPrefix, '', $header));
                }
            } else {
                $tokenString = trim($header);
            }
        }

        return $tokenString;
    }
}
