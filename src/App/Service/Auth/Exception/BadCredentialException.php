<?php

declare(strict_types=1);

namespace App\Service\Auth\Exception;

use Fig\Http\Message\StatusCodeInterface;

class BadCredentialException extends AuthException
{
    public function getReason(): string
    {
        return 'Login / password does not match';
    }

    public function getStatusCode(): int
    {
        return StatusCodeInterface::STATUS_UNAUTHORIZED;
    }
}
