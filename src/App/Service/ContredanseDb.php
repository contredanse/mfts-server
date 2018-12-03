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
        $dsn = $this->getDsn();

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

    public function getDsn(): string
    {
        return sprintf(
            '%s:host=%s;dbname=%s;port=%s',
            $this->params['driver'],
            $this->params['host'],
            $this->params['dbname'],
            $this->params['port']
        );
    }

    public function getUsername(): string
    {
        return $this->params['username'];
    }

    public function getPassword(): string
    {
        return $this->params['password'];
    }

    public function getConnectionInfo(): array
    {
        return [
            'username' => $this->params['username'],
            'password' => $this->params['password'],
            'host'     => $this->params['host'],
            'dbname'   => $this->params['dbname'],
            'port'     => $this->params['port'],
        ];
    }
}
