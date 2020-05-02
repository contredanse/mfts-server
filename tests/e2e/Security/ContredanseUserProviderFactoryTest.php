<?php

declare(strict_types=1);

namespace AppTest\E2E\Security;

use App\Exception\ConnectionException;
use App\Infra\Db\ContredanseDb;
use App\Security\ContredanseUserProvider;
use App\Security\ContredanseUserProviderFactory;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\MiddlewareContainer;

class ContredanseUserProviderFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(MiddlewareContainer::class)->willImplement(ContainerInterface::class);
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
        $this->container->get(ContredanseDb::class)
            ->willReturn(
                new ContredanseDb([
                    'driver'      => 'mysql',
                    'host'        => 'localhost',
                    'dbname'      => 'mfts-db',
                    'port'        => '3306',
                    'username'    => 'cool',
                    'password'    => 'invalid'
                ])
            );

        /* @phpstan-ignore-next-line */
        (new ContredanseUserProviderFactory())($this->
        container->reveal());
    }
}
