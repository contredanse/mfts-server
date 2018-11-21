<?php

declare(strict_types=1);

namespace App\Service\Exception;

class TokenIssuerException extends TokenValidationException
{
    public function getReason(): string
    {
        return 'issuer';
    }
}
