<?php declare(strict_types=1);

namespace App\Service;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Ramsey\Uuid\Uuid;

class TokenManager
{

    private $signer;
    private $issuer;
    private $audience;
    private $privateKey;

    function __construct(string $privateKey)
    {

        $this->signer = new Sha256();
        $this->issuer = $_SERVER['SERVER_NAME'];
        $this->audience = $_SERVER['SERVER_NAME'];
        $this->privateKey = $privateKey;
    }

    function createNewToken(array $customClaims = [], int $expiration = 3600, bool $autoSign = true): Token
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

    function signToken(Builder $builder): Token
    {
        return $builder->sign($this->signer, $this->privateKey)
            ->getToken(); // Retrieves the generated token
    }
}
