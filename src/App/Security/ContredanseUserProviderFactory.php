<?php declare(strict_types=1);

namespace App\Security;

use App\Exception\ConfigException;
use App\Exception\ConnectionException;
use Psr\Container\ContainerInterface;

class ContredanseUserProviderFactory
{
    /**
     * @throws ConfigException
     */
    function __invoke(ContainerInterface $container): ContredanseUserProvider
    {
        $config = $container->get('config')['contredanse'] ?? null;
        if ($config === null) {
            throw new ConfigException("['contredanse'] config key is missing.");
        }
        if (!is_array($config['db'] ?? false)) {
            throw new ConfigException("['contredanse']['db'] config key is missing.");
        }


        return new ContredanseUserProvider(
            $this->getPdoConnection($config['db'])
        );
    }

    /**
     * @param string[] $config
     * @throws ConnectionException
     */
    function getPdoConnection(array $config): \PDO
    {

        $dsn = $config['dsn'];
        $options = $config['driver_options'] ?? null;
        try {
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
        } catch (\Throwable $e) {
            throw new ConnectionException(sprintf(
                'Database connection failure (%s)',
                $e->getMessage()
            ));
        }
        return $pdo;
    }
}
