<?php

declare(strict_types=1);

namespace App\Security\Exception;

class NoProductAccessException extends \RuntimeException implements ProductAccessExceptionInterface
{
}
