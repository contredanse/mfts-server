<?php

declare(strict_types=1);

namespace App\Security\Exception;

class ProductAccessExpiredException extends \RuntimeException implements ProductAccessExceptionInterface
{
}
