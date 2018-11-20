<?php

declare(strict_types=1);

namespace App\Handler;

use App\Security\UserProviderInterface;
use App\Service\TokenManager;
use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\JWT\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

class AuthTokenHandler implements RequestHandlerInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var TokenManager
     */
    private $tokenManager;

    public function __construct(UserProviderInterface $userProvider, TokenManager $tokenManager)
    {
        $this->userProvider = $userProvider;
        $this->tokenManager = $tokenManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        switch ($request->getAttribute('action', 'index')) {
            case 'token':
                return $this->loginAction($request);
            case 'validate':
                return $this->validateAction($request);
            default:
                return (new TextResponse('Not found'))->withStatus(404);
        }
    }

    public function validateAction(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('TODO - Handle error your way ;)');
        }

        $body        = $request->getParsedBody();
        $tokenString = $body['token'] ?? '';

        $tokenParser = new Parser();
        try {
            $token = $tokenParser->parse($tokenString);
        } catch (\Throwable $invalidToken) {
            throw new \RuntimeException('Cannot parse the JWT token', 1, $invalidToken);
        }

        //$token->validate()
    }

    public function loginAction(ServerRequestInterface $request): ResponseInterface
    {
        //$users  = $this->userProvider->getAllUsers();
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('TODO - Handle error your way ;)');
        }

        $body     = $request->getParsedBody();
        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if ($email !== '' && $password !== '') {
            $user = $this->userProvider->getUserByEmail($email);
            if ($user !== null) {
                $dbPassword = $user->getDetail('password');
                if ($dbPassword === $password) {
                    $token = $this->tokenManager->createNewToken([
                        'user_id'  => $user->getIdentity(),
                        'email'    => $email
                    ], 3600);

                    return new JsonResponse([
                        'access_token' => (string) $token,
                        'token_type'   => 'api_auth',
                    ]);
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
}
