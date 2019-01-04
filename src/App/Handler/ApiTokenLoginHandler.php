<?php

declare(strict_types=1);

namespace App\Handler;

use App\Security\ContredanseProductAccess;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductAccessExpiredException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\UserProviderInterface;
use App\Service\Auth\AuthenticationManager;
use App\Service\Auth\Exception\AuthExceptionInterface;
use App\Service\Token\Exception\TokenValidationExceptionInterface;
use App\Service\Token\TokenManager;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

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

    /*
     * @var ContredanseProductAccess
     */
    private $productAccess;

    /**
     * @param array<string, mixed> $authParams
     */
    public function __construct(UserProviderInterface $userProvider, TokenManager $tokenManager, ContredanseProductAccess $productAccess, array $authParams = [])
    {
        $this->userProvider  = $userProvider;
        $this->tokenManager  = $tokenManager;
        $this->authParams    = $authParams;
        $this->productAccess = $productAccess;
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

		// @todo Must be removed when production
		if ($email === 'ilove@contredanse.org' && $password === 'demo') {
			// This is for demo only
			return $this->getResponseWithAccessToken('ilove@contredanse.org', $authExpiry);
		}

		$authenticationManager = new AuthenticationManager($this->userProvider);

		try {
			// Authenticate, wil throw exception if failed
			$user = $authenticationManager->getAuthenticatedUser($email, $password);

			// Ensure authorization
			$this->productAccess->ensureAccess(ContredanseProductAccess::PAXTON_PRODUCT, $user);

			return $this->getResponseWithAccessToken($user->getDetail('user_id'), $authExpiry);
		} catch (AuthExceptionInterface $e) {
			return (new JsonResponse([
				'success' => false,
				'reason'  => $e->getReason()
			]))->withStatus($e->getStatusCode());
		} catch (NoProductAccessException | ProductPaymentIssueException | ProductAccessExpiredException $e) {
			return (new JsonResponse([
				'success' => false,
				'reason'  => $e->getMessage(),
			]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
		} catch (\Throwable $e) {
			return (new JsonResponse([
				'success' => false,
				'reason'  => $e->getMessage()
			]))->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
		}
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
