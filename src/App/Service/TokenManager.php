<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Exception\InvalidTokenException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Ramsey\Uuid\Uuid;

class TokenManager
{
    private $signer;
    private $issuer;
    private $audience;
    private $privateKey;

    public function __construct(string $privateKey)
    {
        $this->signer     = new Sha256();
        $this->issuer     = $_SERVER['SERVER_NAME'];
        $this->audience   = $_SERVER['SERVER_NAME'];
        $this->privateKey = $privateKey;
    }

    public function createNewToken(array $customClaims = [], int $expiration = 3600, bool $autoSign = true): Token
    {
        $builder = (new Builder())
            ->setIssuer($this->issuer) // Configures the issuer (iss claim)
            ->setAudience($this->audience) // Configures the audience (aud claim)
            ->setId(Uuid::uuid1()->toString(), true) // Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setNotBefore(time() + 0) // Configures the time that the token can be used (nbf claim)
            ->setExpiration(time() + $expiration); // Configures the expiration time of the token (exp claim)

        foreach ($customClaims as $key => $value) {
            $builder->set($key, $value);
        }

        if ($autoSign) {
            return $this->signToken($builder);
        }

        return $builder->getToken();
    }

    public function signToken(Builder $builder): Token
    {
        return $builder->sign($this->signer, $this->privateKey)
            ->getToken(); // Retrieves the generated token
    }

    /**
     * @throws InvalidTokenException
     */
    public function parseToken(string $token): Token
    {
        $tokenParser = new Parser();
        try {
            return $tokenParser->parse($token);
        } catch (\Throwable $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function verifySignature(Token $token): bool
    {
        return $token->verify($this->signer, $this->privateKey);
    }

    public function isExpired(Token $token): bool
    {
        return $token->isExpired();
    }
}
