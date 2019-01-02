<?php

declare(strict_types=1);

namespace App\Service\Auth\Exception;

interface AuthExceptionInterface extends \Throwable
{
    public function getReason(): string;

    public function getStatusCode(): int;
}
