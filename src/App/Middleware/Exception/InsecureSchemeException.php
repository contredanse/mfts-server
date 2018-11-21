<?php

declare(strict_types=1);

namespace App\Middleware\Exception;

class InsecureSchemeException extends \RuntimeException implements MiddlewareExceptionInterface
{
}
