<?php

declare(strict_types=1);

namespace App\Service\Exception;

abstract class TokenValidationException extends \RuntimeException implements TokenValidationExceptionInterface
{
    abstract public function getReason(): string;

    public function getStatusCode(): int
    {
        return 401;
    }
}
