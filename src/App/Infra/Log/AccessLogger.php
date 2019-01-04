<?php

declare(strict_types=1);

namespace App\Infra\Log;

use App\Entity\AccessLog;
use Doctrine\ORM\EntityManager;

class AccessLogger
{
    public const TYPE_LOGIN_SUCCESS  = 'log.success';
    public const TYPE_LOGIN_FAILURE  = 'log.fail';
	public const TYPE_TOKEN_VALIDATE = 'log.validate';

    public const SUPPORTED_TYPES = [
        self::TYPE_LOGIN_SUCCESS,
        self::TYPE_LOGIN_FAILURE,
		self::TYPE_TOKEN_VALIDATE,
    ];

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function log(string $type, string $email, ?string $ip): void
    {
        $accessLog = new AccessLog($type, $email, $ip);
        $this->em->persist($accessLog);
    }
}
