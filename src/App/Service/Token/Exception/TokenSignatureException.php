<?php

declare(strict_types=1);

namespace App\Service\Token\Exception;

class TokenSignatureException extends TokenValidationException
{
    public function getReason(): string
    {
        return 'signature';
    }
}
