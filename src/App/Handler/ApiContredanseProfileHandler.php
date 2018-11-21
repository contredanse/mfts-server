<?php

declare(strict_types=1);

namespace App\Handler;

use App\Middleware\AuthTokenMiddleware;
use App\Security\Exception\UserNotFoundException;
use App\Security\UserProviderInterface;
use App\Service\TokenManager;
use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiContredanseProfileHandler implements RequestHandlerInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var TokenManager
     */
    private $tokenManager;

    public function __construct(TokenManager $tokenManager, UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
        $this->tokenManager = $tokenManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token = $request->getAttribute(AuthTokenMiddleware::class);
        if (!$token instanceof Token) {
            return (new JsonResponse([
                'success' => false,
                'reason'  => 'Missing auth middleware attribute',
            ]))->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }

        $user_id = $token->getClaim('user_id', '');

        try {
            $userData = $this->userProvider->findUser($user_id);
            $data     = [
                'success' => true,
                'data'    => [
                    'user_id'   => $userData['user_id'],
                    'firstname' => $userData['firstname'],
                    'name'      => $userData['name'],
                    'email'     => $userData['email'],
                ]
            ];

            return (new JsonResponse($data))->withStatus(StatusCodeInterface::STATUS_OK);
        } catch (UserNotFoundException $e) {
            return (new JsonResponse(['success' => false, 'reason' => $e->getMessage()]))
                ->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        } catch (\Throwable $e) {
            return (new JsonResponse(['success' => false, 'reason' => $e->getMessage()]))
                ->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
