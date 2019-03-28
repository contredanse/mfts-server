<?php

declare(strict_types=1);

namespace App\Handler;

use App\Infra\Log\AccessLogger;
use App\Security\ContredanseProductAccess;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductAccessExpiredException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\UserProviderInterface;
use App\Service\Auth\AuthManager;
use App\Service\Auth\Exception\AuthExceptionInterface;
use App\Service\Token\TokenManager;
use DateTime;
use Fig\Http\Message\StatusCodeInterface;
use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiTokenLoginHandler implements RequestHandlerInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var TokenManager
     */
    private $tokenManager;

    /**
     * @var array<string, mixed>
     */
    private $authParams;

    /**
     * @var ContredanseProductAccess
     */
    private $productAccess;

    /**
     * @var AccessLogger|null
     */
    private $accessLogger;

    /**
     * @param array<string, mixed> $authParams
     */
    public function __construct(UserProviderInterface $userProvider, TokenManager $tokenManager, ContredanseProductAccess $productAccess, array $authParams, ?AccessLogger $accessLogger)
    {
        $this->userProvider  = $userProvider;
        $this->tokenManager  = $tokenManager;
        $this->authParams    = $authParams;
        $this->productAccess = $productAccess;
        $this->accessLogger  = $accessLogger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $authExpiry = $this->authParams['token_expiry'] ?? TokenManager::DEFAULT_EXPIRY;

        $method = $request->getMethod();

        if ($method !== 'POST') {
            throw new \RuntimeException('Unsupported http method');
        }
        // Authorization...
        //
        // Valid users are
        // - either admins
        // - or valid paying users
        //

        $body     = $request->getParsedBody();
        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');
        $language = trim($body['language'] ?? '');
        if ($language === '') {
            $language = $this->getLanguage($request);
        }

        // @todo Must be removed when production
        if ($email === 'ilove@contredanse.org' && $password === 'demo') {
            // This is for demo only
            $this->logAccess($request, AccessLogger::TYPE_LOGIN_SUCCESS, $email, $language);

            return $this->getResponseWithAccessToken($email, $authExpiry);
        }

        $authenticationManager = new AuthManager($this->userProvider);

        try {
            // Authenticate, wil throw exception if failed
            $user = $authenticationManager->getAuthenticatedUser($email, $password);

            // Ensure authorization
            $this->productAccess->ensureAccess(ContredanseProductAccess::PAXTON_PRODUCT, $user);
            $this->logAccess($request, AccessLogger::TYPE_LOGIN_SUCCESS, $email, $language);

            return $this->getResponseWithAccessToken($user->getDetail('user_id'), $authExpiry);
        } catch (\Throwable $e) {
            $type = $this->getAccessLoggerTypeFromException($e);
            $this->logAccess($request, $type, $email, $language);

            $responseData = [
                'success'    => false,
                'reason'     => $e->getMessage(),
                'error_type' => $type,
            ];

            if ($e instanceof ProductAccessExpiredException) {
                $responseData['expired_date'] = $e->getExpiryDate()->format(DateTime::ATOM);
            }

            return (new JsonResponse($responseData))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }
    }

    private function getAccessLoggerTypeFromException(\Throwable $e): string
    {
        switch (true) {
            case $e instanceof AuthExceptionInterface:
                return AccessLogger::TYPE_LOGIN_FAILURE_CREDENTIALS;
            case $e instanceof NoProductAccessException:
                return AccessLogger::TYPE_LOGIN_FAILURE_NO_ACCESS;
            case $e instanceof ProductPaymentIssueException:
                return AccessLogger::TYPE_LOGIN_FAILURE_PAYMENT_ISSUE;
            case $e instanceof ProductAccessExpiredException:
                return AccessLogger::TYPE_LOGIN_FAILURE_EXPIRY;
            default:
                return AccessLogger::TYPE_LOGIN_FAILURE;
        }
    }

    private function logAccess(ServerRequestInterface $request, string $type, string $email, ?string $language): void
    {
        if ($this->accessLogger !== null) {
            ['REMOTE_ADDR' => $ipAddress, 'HTTP_USER_AGENT' => $userAgent] = $request->getServerParams();
            try {
                $this->accessLogger->log(
                    $type,
                    $email,
                    $language,
                    $ipAddress,
                    $userAgent
                );
            } catch (\Throwable $e) {
                // Discard any error
                //var_dump($e->getMessage());
                //die();
                error_log('AuthLoggerMiddleware failure' . $e->getMessage());
            }
        }
    }

    private function getLanguage(ServerRequestInterface $request): ?string
    {
        try {
            $acceptLanguageHeader = trim($request->getHeaderLine('Accept-Language'));

            if (trim($acceptLanguageHeader) !== '') {
                $negotiator = new LanguageNegotiator();
                /**
                 * @var AcceptLanguage|null $acceptLanguage
                 */
                $acceptLanguage = $negotiator->getBest($acceptLanguageHeader, ['fr', 'en']);
                if ($acceptLanguage !== null) {
                    $parts = array_filter([
                        $acceptLanguage->getBasePart(), // lang
                        $acceptLanguage->getSubPart() // region
                    ]);

                    if (count($parts) > 0) {
                        return implode('_', $parts);
                    }
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function getResponseWithAccessToken(string $user_id, int $authExpiry): ResponseInterface
    {
        $token = $this->tokenManager->createNewToken([
            'user_id' => $user_id
            //'email'    => $email,
        ], $authExpiry);

        return (new JsonResponse([
            'access_token' => (string) $token,
            'token_type'   => 'api_auth',
            'success'      => true,
        ]))->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
