<?php

declare(strict_types=1);

namespace AppTest\Functional\Security;

use App\Exception\ConnectionException;
use App\Security\ContredanseUserProvider;
use App\Security\ContredanseUserProviderFactory;
use App\Service\ContredanseDb;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContredanseUserProviderFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface> */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testShouldWorkWithRegisteredContainer(): void
    {
        $container    = ContainerFactory::getContainer();
        $userProvider = (new ContredanseUserProviderFactory())($container);
        self::assertInstanceOf(ContredanseUserProvider::class, $userProvider);
    }

    public function testMustThrowConnectionException(): void
    {
        self::expectException(ConnectionException::class);
        $this->container->get('config')
            ->willReturn([
                'contredanse' => [
                    'db' => [
                        'dsn'      => 'mysql:host=localhost;dbname=mfts-db;port=3306',
                        'username' => 'cool',
                        'password' => 'invalid'
                     ]
                ]
            ]);
        $this->container->get(ContredanseDb::class)
            ->willReturn(
                new ContredanseDb([
                    'dsn'      => 'mysql:host=localhost;dbname=mfts-db;port=3306',
                    'username' => 'cool',
                    'password' => 'invalid'
                ])
            );

        (new ContredanseUserProviderFactory())($this->
        container->reveal());
    }
}
