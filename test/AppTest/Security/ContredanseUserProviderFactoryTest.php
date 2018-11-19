<?php

declare(strict_types=1);

namespace AppTest\Security;

use App\Exception\ConfigException;
use App\Exception\ConnectionException;
use App\Security\ContredanseUserProviderFactory;
use AppTest\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContredanseUserProviderFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface>  */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testShouldWorkWithRegisteredContainer(): void {
        $container = ContainerFactory::getContainer();
        $userProvider = (new ContredanseUserProviderFactory())($container);
        self::assertNotNull($userProvider);
    }

    public function testMustThrowConfigException(): void {
        self::expectException(ConfigException::class);
        (new ContredanseUserProviderFactory())($this->container->reveal());
    }

    public function testMustThrowConnectionException(): void {
        self::expectException(ConnectionException::class);
        $this->container->get('config')
            ->willReturn([
                'contredanse' => [
                    'db' => [
                        'driver' => 'mysql',
                        'hostname' => 'localhost',
                        'username' => 'cool',
                        'database' => 'test',
                        'password' => 'invalid'
                     ]
                ]
            ]);

        (new ContredanseUserProviderFactory())($this->
        container->reveal());
    }

}
