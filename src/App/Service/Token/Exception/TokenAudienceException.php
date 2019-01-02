<?php

declare(strict_types=1);

namespace App\Service\Token\Exception;

class TokenAudienceException extends TokenValidationException
{
    public function getReason(): string
    {
        return 'audience';
    }

    public function getStatusCode(): int
    {
        // Forbidden
        return 403;
    }
}
