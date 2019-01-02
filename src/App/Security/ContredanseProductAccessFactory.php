<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\ConfigException;
use App\Service\Db\ContredanseDb;
use Psr\Container\ContainerInterface;

class ContredanseProductAccessFactory
{
    /**
     * @throws ConfigException
     */
    public function __invoke(ContainerInterface $container): ContredanseProductAccess
    {
        $config = $container->get('config')['contredanse'] ?? null;
        if ($config === null) {
            throw new ConfigException("['contredanse'] config key is missing.");
        }
        if (!is_array($config['products'] ?? false)) {
            throw new ConfigException("['contredanse']['products'] config key is missing.");
        }

        return new ContredanseProductAccess(
            $container->get(ContredanseDb::class)->getPdoAdapter(),
            $config['products']
        );
    }
}
