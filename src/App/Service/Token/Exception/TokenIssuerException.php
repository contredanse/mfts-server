<?php

declare(strict_types=1);

namespace App\Service\Token\Exception;

class TokenIssuerException extends TokenValidationException
{
    public function getReason(): string
    {
        return 'issuer';
    }

    public function getStatusCode(): int
    {
        // Forbidden
        return 403;
    }
}
