<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ConnectionException;

class ContredanseDb
{
    /**
     * @var \PDO
     */
    private $pdo;

    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @throws ConnectionException
     */
    public function getPdoAdapter(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->createPdoConnection($this->params);
        }

        return $this->pdo;
    }

    /**
     * @param string[] $config
     *
     * @throws ConnectionException
     */
    public function createPdoConnection(array $config): \PDO
    {
        $dsn = $config['dsn'];
        /** @var string[] $options */
        $options = $config['driver_options'] ?? null;

        try {
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $e) {
            throw new ConnectionException(sprintf(
                'Database connection failure (%s)',
                $e->getMessage()
            ));
        }

        return $pdo;
    }
}
