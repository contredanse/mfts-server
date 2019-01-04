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

class ApiTokenValidateHandler implements RequestHandlerInterface
{
	/**
     * @var TokenManager
     */
    private $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager  = $tokenManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

		$method = $request->getMethod();
		if ($method !== 'POST') {
			throw new \RuntimeException('TODO - Handle error your way ;)');
		}
		$body        = $request->getParsedBody();
		$tokenString = $body['token'] ?? '';
		try {
			$token = $this->tokenManager->getValidatedToken($tokenString);

			return (new JsonResponse([
				'valid' => true,
				'data'  => [
					'user_id'        => $token->getClaim('user_id'),
					'expires_at'     => $token->getClaim('exp'),
					'remaining_time' => $token->getClaim('exp') - time(),
				]
			]))->withStatus(StatusCodeInterface::STATUS_OK);
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

}
