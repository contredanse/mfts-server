<?php

declare(strict_types=1);

namespace App\Security\Exception;

use DateTimeImmutable;

class ProductAccessExpiredException extends \RuntimeException implements ProductAccessExceptionInterface
{
    /**
     * @var DateTimeImmutable
     */
    private $expiryDate;

    public function __construct(string $message, DateTimeImmutable $expiryDate)
    {
        parent::__construct($message);
        $this->expiryDate = $expiryDate;
    }

    public function getExpiryDate(): DateTimeImmutable
    {
        return $this->expiryDate;
    }
}
