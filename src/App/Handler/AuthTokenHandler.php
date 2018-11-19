<?php

declare(strict_types=1);

namespace App\Handler;

use App\Security\UserProviderInterface;
use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Zend\Diactoros\Response\TextResponse;

class AuthTokenHandler implements RequestHandlerInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
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

    function validateAction(ServerRequestInterface $request): JsonResponse
    {
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('TODO - Handle error your way ;)');
        }

        $body = $request->getParsedBody();
        $tokenString = $body['token'] ?? '';


        $tokenParser = new Parser();
        try {
            $token = $tokenParser->parse($tokenString);
        } catch (\Throwable $invalidToken) {
            throw new \RuntimeException('Cannot parse the JWT token', 1, $invalidToken);
        }


        //$token->validate()
    }

    function loginAction(ServerRequestInterface $request): JsonResponse
    {

        //$users  = $this->userProvider->getAllUsers();
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('TODO - Handle error your way ;)');
        }

        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        $user = $this->userProvider->getUserByEmail($login);

        if ($user === null || $user->getDetail('password') !== $password) {
            return (new JsonResponse([
                'success' => false,
                'reason' => $user === null ?
                    'User does not exists' :
                    'Password invalid'
            ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }
        $token = $this->createToken([
            'login'  => $login
        ]);
        return new JsonResponse([
            'access_token' => (string) $token,
            'token_type'   => 'api_auth',
        ]);
    }


    function createToken(array $customClaims = []): Token
    {
        $issuer = $_SERVER['SERVER_NAME'];
        $audience = $_SERVER['SERVER_NAME'];

        $signer = new Sha256();
        $builder = (new Builder())
            ->setIssuer($issuer) // Configures the issuer (iss claim)
            ->setAudience($audience) // Configures the audience (aud claim)
            ->setId(Uuid::uuid1(), true) // Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setNotBefore(time() + 0) // Configures the time that the token can be used (nbf claim)
            ->setExpiration(time() + 3600); // Configures the expiration time of the token (exp claim)

        foreach ($customClaims as $key => $value) {
            $builder->set($key, $value);
        }

        $token = $builder->sign($signer, 'testing') // creates a signature using "testing" as key
                ->getToken(); // Retrieves the generated token

        return $token;
    }
}
