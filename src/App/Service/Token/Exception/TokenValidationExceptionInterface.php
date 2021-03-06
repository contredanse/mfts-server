<?php

declare(strict_types=1);

namespace App\Service\Token\Exception;

interface TokenValidationExceptionInterface extends \Throwable
{
    public function getReason(): string;

    public function getStatusCode(): int;
}
