<?php

declare(strict_types=1);

namespace App\Service\Token\Exception;

abstract class TokenValidationException extends \RuntimeException implements TokenValidationExceptionInterface
{
    abstract public function getReason(): string;

    public function getStatusCode(): int
    {
        // Unauthorized
        return 401;
    }
}
