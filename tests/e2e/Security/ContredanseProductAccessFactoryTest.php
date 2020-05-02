<?php

declare(strict_types=1);

namespace AppTest\E2E\Security;

use App\Exception\ConfigException;
use App\Infra\Db\ContredanseDb;
use App\Security\ContredanseProductAccess;
use App\Security\ContredanseProductAccessFactory;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContredanseProductAccessFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ContredanseDb::class)
            ->willReturn(
                new ContredanseDb(
                    ContainerFactory::getConfig('contredanse')['db']
                )
            );
    }

    public function testShouldWorkWithRegisteredContainer(): void
    {
        $container     = ContainerFactory::getContainer();
        $productAccess = (new ContredanseProductAccessFactory())($container);
        self::assertTrue(true);
    }

    public function testShouldWorkWithCustomConfig(): void
    {
        $this->container->get('config')
            ->willReturn(
                [
                    'contredanse' => [
                        'products' => [
                            ContredanseProductAccess::PAXTON_PRODUCT => [1, 2]
                        ]
                    ]
                ]
            );

        /* @phpstan-ignore-next-line */
        (new ContredanseProductAccessFactory())->__invoke($this->container->reveal());
        self::assertTrue(true);
    }

    public function testMustThrowConfigException(): void
    {
        self::expectException(ConfigException::class);

        $this->container->get('config')
            ->willReturn(
                [
                    'contredanse' => [
                        //'products' => [
                        //	ContredanseProductAccess::PAXTON_PRODUCT => [1, 2]
                        //]
                    ]
                ]
            );

        /* @phpstan-ignore-next-line */
        (new ContredanseProductAccessFactory())($this->container->reveal());
    }
}
