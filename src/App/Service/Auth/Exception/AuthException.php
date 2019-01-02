<?php

declare(strict_types=1);

namespace App\Service\Auth\Exception;

use Fig\Http\Message\StatusCodeInterface;

class AuthException extends \RuntimeException implements AuthExceptionInterface
{
    public function getReason(): string
    {
        return 'Authentication failure';
    }

    public function getStatusCode(): int
    {
        return StatusCodeInterface::STATUS_UNAUTHORIZED;
    }
}
