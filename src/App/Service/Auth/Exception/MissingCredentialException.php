<?php

declare(strict_types=1);

namespace App\Service\Auth\Exception;

use Fig\Http\Message\StatusCodeInterface;

class MissingCredentialException extends AuthException
{
    public function getReason(): string
    {
        return 'Missing credential exception';
    }

    public function getStatusCode(): int
    {
        return StatusCodeInterface::STATUS_BAD_REQUEST;
    }
}
