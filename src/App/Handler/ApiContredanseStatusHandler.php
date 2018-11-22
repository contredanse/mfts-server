<?php

declare(strict_types=1);

namespace App\Handler;

use App\Security\ContredanseUserProvider;
use App\Service\ContredanseDb;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiContredanseStatusHandler implements RequestHandlerInterface
{
    /**
     * @var ContredanseDb
     */
    private $db;

    public function __construct(ContredanseDb $contredanseDb)
    {
        $this->db = $contredanseDb;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Special try/catch to prevent showing credentials
            $pdo = $this->db->getPdoAdapter();
        } catch (\Throwable $e) {
            return (new JsonResponse([
                'up'      => false,
                'ack'     => time(),
                'reason'  => 'Database connection failure'
            ]))->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }

        try {
            $userProvider = new ContredanseUserProvider($pdo);
            $userProvider->ensureAuthIsWorking();

            return (new JsonResponse([
                'up'      => true,
                'ack'     => time(),
            ]))->withStatus(StatusCodeInterface::STATUS_OK);
        } catch (\Throwable $e) {
            return (new JsonResponse([
                'up'      => false,
                'ack'     => time(),
                'reason'  => $e->getMessage()
            ]))->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
