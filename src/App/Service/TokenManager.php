<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Exception\TokenAudienceException;
use App\Service\Exception\TokenExpiredException;
use App\Service\Exception\TokenIssuerException;
use App\Service\Exception\TokenParseException;
use App\Service\Exception\TokenSignatureException;
use App\Service\Exception\TokenValidationExceptionInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\BaseSigner;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Ramsey\Uuid\Uuid;

class TokenManager
{
    public const DEFAULT_EXPIRY = 3600;

    /**
     * @var BaseSigner
     */
    private $signer;
    /**
     * @var string
     */
    private $issuer;
    /**
     * @var string
     */
    private $audience;
    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var int
     */
    private $defaultExpiry;

    public function __construct(string $privateKey, int $defaultExpiry = self::DEFAULT_EXPIRY)
    {
        $this->signer        = $this->getSigner();
        $this->issuer        = $_SERVER['SERVER_NAME'];
        $this->audience      = $_SERVER['SERVER_NAME'];
        $this->privateKey    = $privateKey;
        $this->defaultExpiry = $defaultExpiry;
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

    /**
     * Sign the token.
     */
    public function signToken(Builder $builder): Token
    {
        return $builder->sign($this->signer, $this->privateKey)
            ->getToken(); // Retrieves the generated token
    }

    /**
     * @throws TokenParseException
     */
    public function parseToken(string $tokenString): Token
    {
        $tokenParser = new Parser();
        try {
            return $tokenParser->parse($tokenString);
        } catch (\Throwable $e) {
            throw new TokenParseException($e->getMessage());
        }
    }

    /**
     * Ensure that the token signature is valid and
     * the token have not been tampered.
     *
     * @throws TokenSignatureException
     */
    public function ensureValidSignature(Token $token): void
    {
        if (!$this->verifySignature($token)) {
            throw new TokenSignatureException(sprintf(
                'Token failed signature verification.'
            ));
        }
    }

    /**
     * @throw TokenExpiredException
     */
    public function ensureNotExpired(Token $token): void
    {
        if ($this->isExpired($token)) {
            throw new TokenExpiredException(sprintf(
                'Token validity has expired.'
            ));
        }
    }

    /**
     * @throws TokenValidationExceptionInterface the main one
     * @throws TokenParseException
     * @throws TokenExpiredException
     * @throws TokenSignatureException
     * @throws TokenIssuerException
     * @throws TokenAudienceException
     */
    public function getValidatedToken(string $tokenString): Token
    {
        $token = $this->parseToken($tokenString);

        $this->ensureValidSignature($token);
        $this->ensureNotExpired($token);

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($this->issuer);
        $data->setAudience($this->audience);

        if ($token->hasClaim('iss')) {
            $issuer = $token->getClaim('iss', false);
            if ($issuer !== $this->issuer) {
                throw new TokenIssuerException(sprintf(
                    'Token issuer does not match'
                ));
            }
        }

        if ($token->hasClaim('aud')) {
            $issuer = $token->getClaim('aud', false);
            if ($issuer !== $this->issuer) {
                throw new TokenAudienceException(sprintf(
                    'Token audience does not match'
                ));
            }
        }

        return $token;
    }

    public function getSigner(): BaseSigner
    {
        return new Sha256();
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
