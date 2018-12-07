<?php

declare(strict_types=1);

namespace App\Handler;

use App\Security\UserProviderInterface;
use App\Service\Exception\TokenValidationExceptionInterface;
use App\Service\TokenManager;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

class ApiAuthTokenHandler implements RequestHandlerInterface
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
     * @param array<string, mixed> $authParams
     */
    public function __construct(UserProviderInterface $userProvider, TokenManager $tokenManager, array $authParams = [])
    {
        $this->userProvider = $userProvider;
        $this->tokenManager = $tokenManager;
        $this->authParams   = $authParams;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        switch ($request->getAttribute('action', 'index')) {
            case 'token':
                return $this->loginAction($request);
            case 'validate':
                return $this->validateAction($request);
            default:
                return (new TextResponse('Not found'))
                    ->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function validateAction(ServerRequestInterface $request): ResponseInterface
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

    public function loginAction(ServerRequestInterface $request): ResponseInterface
    {
        $authExpiry = $this->authParams['token_expiry'] ?? TokenManager::DEFAULT_EXPIRY;

        //$users  = $this->userProvider->getAllUsers();
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('Unsupported method');
        }

        $body     = $request->getParsedBody();
        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if ($email === 'ilove@contredanse.org' && $password === 'demo') {
            // This is for demo only
            return $this->getResponseWithAccessToken('ilovecontredanse.org', $authExpiry);
        } elseif ($email !== '' && $password !== '') {
            $user = $this->userProvider->getUserByEmail($email);
            if ($user !== null) {
                $dbPassword = $user->getDetail('password');
                if ($dbPassword === $password) {
                    $roles = $user->getRoles();

                    // Only admins for now !
                    if (in_array('admin', (array) $roles, true)) {
                        return $this->getResponseWithAccessToken($user->getDetail('user_id'), $authExpiry);
                    }
                }
            }

            return (new JsonResponse([
                'success' => false,
                'reason'  => $user === null ?
                    'User does not exists' :
                    'Password invalid'
            ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        return (new JsonResponse([
            'success' => false,
            'reason'  => 'Missing parameter'
        ]))->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    public function getResponseWithAccessToken(string $user_id, int $authExpiry): ResponseInterface
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
